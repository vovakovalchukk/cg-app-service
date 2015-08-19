<?php
namespace CG\Product\Service;

use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as ProductEntity;
use CG\Product\Filter;
use CG\Product\Gearman\ClientAwareInterface as GearmanClientAwareInterface;
use CG\Product\Gearman\Workload\UpdateItemsTaxFactoryAwareInterface;
use CG\Product\Gearman\Workload\UpdateItemsTaxFactoryInterface;
use CG\Product\ServiceAbstract;
use CG\Stdlib\Exception\Runtime\NotFound;
use GearmanClient;

class Service extends ServiceAbstract implements GearmanClientAwareInterface, UpdateItemsTaxFactoryAwareInterface
{
    const REMOVING_PRODUCT = "Removing product %s";
    const REMOVING_STOCK = "Last product of SKU: %s deleted for OU: %s; deleting corresponding stock";
    const NOT_REMOVING_STOCK = "Not deleting stock for SKU: %s, OU: %s; other products exists with sku";
    const LOG_CODE = 'ProductService';
    const LOG_MSG_TAXRATEID_NOT_CHANGED = 'The taxRateId for Product %d, OU %d has not changed';
    const LOG_MSG_TAXRATEID_CHANGED = 'The taxRateId for Product %d, OU %d has changed (%s => %s), will trigger Item tax update';
    const LOG_MSG_TAXRATEID_NON_PARENT = 'Product %d, OU %d is a non-parent product, will use it directly in the tax update';
    const LOG_MSG_TAXRATEID_PARENT = 'Product %d, OU %d is a parent product, will use its variations in the tax update';
    const LOG_UPDATE_TAX_OU_SKU = "Creating updateItemsTax job for sku %s, ou %s";

    protected $gearmanClient;
    protected $updateItemsTaxWorkloadFactory;

    public function fetch($id)
    {
        $entity = $this->getRepository()->fetch($id);
        //Converting to Collection removes need for duplicate code throughout the codebase
        $productCollection = new ProductCollection(ProductEntity::class, __FUNCTION__, compact('id'));
        $productCollection->attach($entity);
        $this->fetchCollectionEmbeds($productCollection);
        return $entity;
    }

    public function fetchCollectionByOUAndId(array $organisationUnitId, array $id)
    {
        return $this->fetchCollectionByFilter(
            (new Filter)
                ->setOrganisationUnitId($organisationUnitId)
                ->setId($id)
        );
    }

    public function fetchCollectionByOUAndSku(array $organisationUnitId, array $sku)
    {
        return $this->fetchCollectionByFilter(
            (new Filter)
                ->setOrganisationUnitId($organisationUnitId)
                ->setSku($sku)
        );
    }

    public function remove($entity)
    {
        $parentProductId = [$entity->getId()];
        $this->logInfo(static::REMOVING_PRODUCT, $parentProductId, ["Product", "RemovingStock"]);
        try {
            $filter = new Filter('all', 1, [], null, $parentProductId);
            $variations = $this->fetchCollectionByFilter($filter);
            foreach ($variations as $variation) {
                $this->remove($variation);
            }
        } catch (NotFound $e) {
            // NoOp
        }

        try {
            $this->getListingStorage()->removeByProductId($entity->getId());
        } catch (NotFound $e) {
            //Ignore Not Found Errors
        }

        $this->getRepository()->remove($entity);

        $sku = [$entity->getSku()];
        $organisationUnitId = [$entity->getOrganisationUnitId()];
        try {
            $filter = new Filter('all', 1, $organisationUnitId);
            $filter->setSku($sku);
            $products = $this->fetchCollectionByFilter($filter);
            if ($products->count() == 1) {
                // Check returned product isn't what we just deleted, incase cache has not been cleared yet
                $products->rewind();
                if ($products->current()->getId() == $entity->getId()) {
                    throw new NotFound();
                }
            }
            $this->logDebug(static::NOT_REMOVING_STOCK, [$sku[0], $organisationUnitId[0]], ["Product", "RemovingStock"]);
        } catch (NotFound $e) {
            $this->logInfo( static::REMOVING_STOCK, [$sku[0], $organisationUnitId[0]], ["Product", "RemovingStock"]);
            try {
                $stocks = $this->getStockStorage()->fetchCollectionBySKUs($sku, $organisationUnitId);
                foreach ($stocks as $stock){
                    $this->getStockStorage()->remove($stock);
                }
            } catch (NotFound $e) {
                // NoOp
            }
        }
    }

    public function save($product)
    {
        $existingProduct = null;
        try {
            if ($product->getId()) {
                $existingProduct = $this->fetch($product->getId());
            }
        } catch (NotFound $e) {
            // No-op
        }
        $response = parent::save($product);
        $this->updateRelatedItemsTax($product, $existingProduct);
        return $response;
    }

    protected function updateRelatedItemsTax($product, $existingProduct = null)
    {
        $originalTaxRateId = ($existingProduct ? $existingProduct->getTaxRateId() : '');
        if ($originalTaxRateId == $product->getTaxRateId()) {
            $this->logDebug(static::LOG_MSG_TAXRATEID_NOT_CHANGED, [$product->getId(), $product->getOrganisationUnitId()], static::LOG_CODE);
            return;
        }
        $this->logDebug(static::LOG_MSG_TAXRATEID_CHANGED, [$product->getId(), $product->getOrganisationUnitId(), $originalTaxRateId, $product->getTaxRateId()], static::LOG_CODE);
        if (!$product->isParent()) {
            $this->logDebug(static::LOG_MSG_TAXRATEID_NON_PARENT, [$product->getId(), $product->getOrganisationUnitId()], static::LOG_CODE);
            return $this->updateRelatedItemsTaxForSku($product->getSku(), $product->getOrganisationUnitId());
        }
        $this->logDebug(static::LOG_MSG_TAXRATEID_PARENT, [$product->getId(), $product->getOrganisationUnitId()], static::LOG_CODE);
        foreach ($product->getVariations() as $variation) {
            $this->updateRelatedItemsTaxForSku($variation->getSku(), $variation->getOrganisationUnitId());
        }
    }

    protected function updateRelatedItemsTaxForSku($sku, $organisationUnitId)
    {
        $this->logDebug(static::LOG_UPDATE_TAX_OU_SKU, [$sku, $organisationUnitId], [static::LOG_CODE, "CreateTaxJob"], ["sku" => $sku, "ou" => $organisationUnitId]);
        $workloadFactory = $this->updateItemsTaxWorkloadFactory;
        $workload = $workloadFactory($organisationUnitId, $sku);
        $functionName = $workload->getWorkerFunctionName();
        $jobHandle = $functionName.'-'.$organisationUnitId.'-'.$sku;
        return $this->gearmanClient->doBackground($workload->getWorkerFunctionName(), serialize($workload), $jobHandle);
    }

    public function setGearmanClient(GearmanClient $gearmanClient)
    {
        $this->gearmanClient = $gearmanClient;
        return $this;
    }

    public function setUpdateItemsTaxWorkloadFactory(UpdateItemsTaxFactoryInterface $updateItemsTaxWorkloadFactory)
    {
        $this->updateItemsTaxWorkloadFactory = $updateItemsTaxWorkloadFactory;
        return $this;
    }
}

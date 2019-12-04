<?php
namespace CG\Product\Detail;

use CG\Order\Client\Gearman\Generator\CalculateOrderWeightForSku as CalculateOrderWeightForSkuGearmanJobGenerator;
use CG\Order\Client\Gearman\Generator\UpdateItemsSupplier as UpdateItemsSupplierGearmanJobGenerator;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Slim\Patch\ServiceTrait as PatchTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\EventManager\GlobalEventManager as EventManager;

class RestService extends Service
{
    use PatchTrait;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /** @var EventManager $eventManager */
    protected $eventManager;
    /** @var CalculateOrderWeightForSkuGearmanJobGenerator */
    protected $calculateOrderWeightForSkuGearmanJobGenerator;
    /** @var UpdateItemsSupplierGearmanJobGenerator */
    protected $updateItemsSupplierGearmanJobGenerator;

    public function __construct(
        EventManager $eventManager,
        StorageInterface $repository,
        Mapper $mapper,
        CalculateOrderWeightForSkuGearmanJobGenerator $calculateOrderWeightForSkuGearmanJobGenerator,
        UpdateItemsSupplierGearmanJobGenerator $updateItemsSupplierGearmanJobGenerator
    ) {
        parent::__construct($repository, $mapper);
        $this->eventManager = $eventManager;
        $this->calculateOrderWeightForSkuGearmanJobGenerator = $calculateOrderWeightForSkuGearmanJobGenerator;
        $this->updateItemsSupplierGearmanJobGenerator = $updateItemsSupplierGearmanJobGenerator;
    }

    public function fetchCollectionByFilterAsHal(Filter $filter)
    {
        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }

        $collection = $this->fetchCollectionByFilter($filter);
        return $this->getMapper()->collectionToHal(
            $collection, "/productDetail", $filter->getLimit(), $filter->getPage(), $filter->toArray()
        );
    }

    /**
     * @param ProductDetail $entity
     */
    public function save($entity)
    {
        try {
            $previousEntity = null;
            /** @var ProductDetail $previousEntity */
            try {
                if (is_null($entity->getId())) {
                    throw new NotFound();
                }
                $previousEntity = $this->fetch($entity->getId());
            } catch (NotFound $exception) {
                //noop
            }
            return parent::save($entity);
        } finally {
            if ($previousEntity === null || $previousEntity->getWeight() !== $entity->getWeight()) {
                $this->calculateOrderWeightForSkuGearmanJobGenerator->generateJobForProductDetail($entity);
            }
            if ($this->shouldUpdateItemsSupplier($entity, $previousEntity)) {
                ($this->updateItemsSupplierGearmanJobGenerator)($entity->getOrganisationUnitId(), $entity->getSku(), $entity->getSupplierId());
            }
        }
    }

    protected function shouldUpdateItemsSupplier(ProductDetail $entity, ?ProductDetail $previousEntity = null): bool
    {
        // New entity with a supplier set
        if ($previousEntity === null && $entity->getSupplierId() != null) {
            return true;
        }
        // Existing entity with supplier changed
        if ($previousEntity && $previousEntity->getSupplierId() != $entity->getSupplierId()) {
            return true;
        }
        return false;
    }

    public function remove(ProductDetail $entity)
    {
        try {
            parent::remove($entity);
        } finally {
            $this->calculateOrderWeightForSkuGearmanJobGenerator->generateJobForProductDetail($entity);
        }
    }

    /**
     * Required by PatchTrait
     * @return EventManager
     */
    protected function getEventManager()
    {
        return $this->eventManager;
    }
}

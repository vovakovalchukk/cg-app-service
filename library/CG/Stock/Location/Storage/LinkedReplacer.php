<?php
namespace CG\Stock\Location\Storage;

use CG\Http\StatusCode;
use CG\Product\LinkLeaf\Collection as ProductLinkLeafs;
use CG\Product\LinkLeaf\Entity as ProductLinkLeaf;
use CG\Product\LinkLeaf\Filter as ProductLinkLeafFilter;
use CG\Product\LinkLeaf\StorageInterface as ProductLinkLeafStorage;
use CG\Stdlib\CollectionInterface;
use CG\Stdlib\Exception\Runtime\MixedResultsException;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Collection as StockCollection;
use CG\Stock\Entity as Stock;
use CG\Stock\Filter as StockFilter;
use CG\Stock\Location\Collection;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\Filter;
use CG\Stock\Location\LinkedCollection;
use CG\Stock\Location\LinkedLocation;
use CG\Stock\Location\QuantifiedLocation;
use CG\Stock\Location\StorageInterface;
use CG\Stock\Location\TypedEntity;
use CG\Stock\StorageInterface as StockStorage;

class LinkedReplacer implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE_RECURSIVE_FETCH = 'Recursive fetch detected - stock location will be replaced with a zero\'d entity';
    const LOG_MSG_RECURSIVE_FETCH = 'Recursive fetch detected - stock location %d (%s) will be replaced with a zero\'d entity';

    /** @var StorageInterface $locationStorage */
    protected $locationStorage;
    /** @var StockStorage $stockStorage */
    protected $stockStorage;
    /** @var ProductLinkLeafStorage $productLinkLeafStorage */
    protected $productLinkLeafStorage;

    public function __construct(
        StorageInterface $locationStorage,
        StockStorage $stockStorage,
        ProductLinkLeafStorage $productLinkLeafStorage
    ) {
        $this->locationStorage = $locationStorage;
        $this->stockStorage = $stockStorage;
        $this->productLinkLeafStorage = $productLinkLeafStorage;
    }

    public function fetch($id)
    {
        return $this->getQuantifiedStockLocation(
            $this->locationStorage->fetch($id)
        );
    }

    /**
     * @param StockLocation $stockLocation
     */
    public function remove($stockLocation)
    {
        $this->locationStorage->remove($stockLocation);
    }

    public function save($stockLocation, array $adjustmentIds = [])
    {
        $quantifiedStockLocation = $this->getQuantifiedStockLocation($stockLocation);
        $quantifiedStockLocation->setOnHand($stockLocation->getOnHand())->setAllocated($stockLocation->getAllocated());

        if (!($quantifiedStockLocation instanceof LinkedLocation)) {
            return $this->locationStorage->save($quantifiedStockLocation, $adjustmentIds);
        }

        if (
            ($quantifiedStockLocation instanceof LinkedLocation)
            && !empty($missingStockLocationSkus = $quantifiedStockLocation->getLinkedLocations()->getMissingSkus())
        ) {
            throw (new ValidationMessagesException(StatusCode::UNPROCESSABLE_ENTITY))->addErrorWithField(
                'stockSku',
                sprintf(
                    'You can not update linked stock location %s because the following stock location sku(s) are missing: %s',
                    $stockLocation->getId(),
                    implode(', ', $missingStockLocationSkus)
                )
            );
        }

        try {
            $fetchedStockLocation = $this->fetch($stockLocation->getId());
        } catch (NotFound $exception) {
            $fetchedStockLocation = $this->getQuantifiedStockLocation($stockLocation);
        }

        if (empty($difference = $this->calculateDifference($fetchedStockLocation, $stockLocation))) {
            return $quantifiedStockLocation;
        }

        $this->saveLinkedStockLocations($quantifiedStockLocation, $difference, $adjustmentIds);

        return $quantifiedStockLocation;
    }

    protected function saveLinkedStockLocations(
        LinkedLocation $quantifiedStockLocation,
        array $difference,
        array $adjustmentIds
    ): void {
        /** @var StockLocation $linkedLocation */
        foreach ($quantifiedStockLocation->getLinkedLocations() as $linkedLocation) {
            $this->applyDifference($linkedLocation, $difference);
            $this->locationStorage->save(
                $linkedLocation,
                $this->buildAdjustmentIdsArrayForLinkedLocation($linkedLocation, $adjustmentIds)
            );
        }
    }

    protected function buildAdjustmentIdsArrayForLinkedLocation(
        StockLocation $linkedLocation,
        array $adjustmentIds
    ): array {
        return array_map(
            function($adjustmentId) use ($linkedLocation) {
                return $adjustmentId . '-' . $linkedLocation->getId();
            },
            $adjustmentIds
        );
    }

    protected function calculateDifference(StockLocation $previous, StockLocation $current): array
    {
        $difference = [];
        foreach (['OnHand', 'Allocated'] as $stock) {
            $stockDifference = $current->{'get' . $stock}() - $previous->{'get' . $stock}();
            if ($stockDifference != 0) {
                $difference[$stock] = $stockDifference;
            }
        }
        return $difference;
    }

    protected function applyDifference(StockLocation $stockLocation, array $difference)
    {
        $componentMultiplier = ($stockLocation instanceof QuantifiedLocation) ? $stockLocation->getComponentMultiplier() : 1;
        foreach ($difference as $stock => $stockDifference) {
            $currentStock = $stockLocation->{'get' . $stock}(false);
            $stockLocation->{'set' . $stock}($currentStock + ($componentMultiplier * $stockDifference), false);
        }
    }

    /**
     * @param Collection $collection
     */
    public function saveCollection(CollectionInterface $collection)
    {
        $savedCollection = new Collection(
            $collection->getEntityClass(),
            $collection->getSourceDescription(),
            $collection->getSourceFilters()
        );
        $exceptions = [];

        foreach ($collection as $location) {
            try {
                $savedCollection->attach($this->save($location));
            } catch (\Exception $exception) {
                $exceptions[] = $exception;
            }
        }

        if (empty($exception)) {
            return $savedCollection;
        }

        throw (new MixedResultsException('Failed to save all linked stock locations'))
            ->setSuccessfulCollection($savedCollection)
            ->setFailureExceptions($exceptions);
    }

    public function fetchCollectionByStockIds(array $stockIds)
    {
        return $this->fetchCollectionByFilter((new Filter('all', 1))->setStockId($stockIds));
    }

    public function fetchCollectionByPaginationAndFilters($limit, $page, array $stockId, array $locationId)
    {
        return $this->fetchCollectionByFilter(new Filter($limit, $page, $stockId, $locationId));
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        echo __METHOD__ . "\n";

        return $this->getQuantifiedStockLocations(
            $this->locationStorage->fetchCollectionByFilter($filter)
        );
    }

    protected function getQuantifiedStockLocation(StockLocation $stockLocation): QuantifiedLocation
    {
        try {
            $productLinkLeaf = $this->getProductLinkLeaf($stockLocation);
            return $this->getLinkedStockLocation(
                $stockLocation,
                $productLinkLeaf,
                $this->getLinkedStockLocations($stockLocation, $productLinkLeaf)
            );
        } catch (NotFound $exception) {
            return $this->buildQuantifiedStockLocation($stockLocation);
        }
    }

    protected function getProductLinkLeaf(StockLocation $stockLocation): ProductLinkLeaf
    {
        if (($stockLocation instanceof TypedEntity) && $stockLocation->getType() != TypedEntity::TYPE_LINKED) {
            throw new NotFound(
                sprintf(
                    'Stock location %s is not a %s entity, but a %s entity',
                    $stockLocation->getId(),
                    TypedEntity::TYPE_LINKED,
                    $stockLocation->getType()
                )
            );
        }

        /** @var Stock $stock */
        $stock = $this->stockStorage->fetch($stockLocation->getStockId());
        return $this->productLinkLeafStorage->fetch(ProductLinkLeaf::generateId($stock->getOrganisationUnitId(), $stock->getSku()));
    }

    protected function getLinkedStockLocations(
        StockLocation $stockLocation,
        ProductLinkLeaf $productLinkLeaf
    ): Collection {
        $ouSkuMap = [];
        foreach ($productLinkLeaf->getStockSkuMap() as $sku => $qty) {
            $ouSkuMap[] = ProductLinkLeaf::generateId($productLinkLeaf->getOrganisationUnitId(), $sku);
        }

        if (empty($ouSkuMap)) {
            throw new NotFound('No linked skus');
        }

        return $this->locationStorage->fetchCollectionByFilter(
            (new Filter('all', 1))->setLocationId([$stockLocation->getLocationId()])->setOuIdSku($ouSkuMap)
        );
    }

    protected function getLinkedStockLocation(
        StockLocation $stockLocation,
        ProductLinkLeaf $productLinkLeaf,
        Collection $linkedStockLocations
    ): LinkedLocation {

        echo __METHOD__ . "\n";

//        echo "\nSTOCK LOCATION\n";
//        print_r($stockLocation);

        return new LinkedLocation(
            $stockLocation->getId(),
            $stockLocation->getStockId(),
            $stockLocation->getLocationId(),
            $this->buildQuantifiedLinkedStockLocations(
                $stockLocation,
                $productLinkLeaf,
                $linkedStockLocations
            )
        );
    }

    protected function getQuantifiedStockLocations(Collection $stockLocations): Collection
    {
        echo __METHOD__ . "\n";

        echo "STOCK LOCATIONS"; //@todo compare how it works in normal case!!!!
        print_r($stockLocations);

        try {
            /** @var StockCollection $stockCollection */
            $stockCollection = $this->stockStorage->fetchCollectionByFilter(
                (new StockFilter('all', 1))->setId($stockLocations->getArrayOf('stockId'))
            );

//            print_r($stockCollection);

        } catch (NotFound $exception) {
            return $this->buildQuantifiedStockLocations($stockLocations);
        }

        try {
            $productLinkLeafs = $this->getProductLinkLeafs($stockCollection, $stockLocations);

//            print_r($productLinkLeafs);
        } catch (NotFound $exception) {
            return $this->buildQuantifiedStockLocations($stockLocations);
        }

        try {
            /** @var Collection $linkedStockLocations */
            $linkedStockLocations = $this->locationStorage->fetchCollectionByFilter(
                $this->getLinkedStockLocationsFilter($stockLocations, $productLinkLeafs)
            );

//            print_r($linkedStockLocations);

            /** @var StockCollection $linkedStock */
            $linkedStock = $this->stockStorage->fetchCollectionByFilter(
                (new StockFilter('all', 1))->setId($linkedStockLocations->getArrayOf('stockId'))
            );

            echo "\n\nLLINKED STOCK\n\n";
//            print_r($linkedStock);

        } catch (NotFound $exception) {
            $linkedStockLocations = new Collection(StockLocation::class, __FUNCTION__, ['id' => $stockLocations->getIds()]);
            $linkedStock = new StockCollection(Stock::class, __FUNCTION__, ['id' => []]);
        }

        $productLinkLeafsByOuAndSku = $this->keyProductLinkLeafsByOuAndSku($productLinkLeafs);
        $linkedStockLocationsByLocationOuAndSku = $this->keyLinkedStockLocationsByLocationOuAndSku(
            $linkedStock,
            $linkedStockLocations
        );

        echo "\n\nlinkedStockLocationsByLocationOuAndSku\n\n";
        print_r($linkedStockLocationsByLocationOuAndSku);

        echo "\n=================================================\n";

        $quantifiedStockLocations = new Collection(
            StockLocation::class,
            $stockLocations->getSourceFilters(),
            $stockLocations->getSourceDescription()
        );
        $quantifiedStockLocations->setTotal($stockLocations->getTotal());

        /** @var StockLocation $stockLocation */
        foreach ($stockLocations as $stockLocation) {

//            echo "\n\nstockLocation\n\n";
//            print_r($stockLocation);

            $stock = $stockCollection->getById($stockLocation->getStockId());
            if (!($stock instanceof Stock)) {
//                echo "\nSTOCK CONTINUE \n";
                continue;
            }

            $key = ProductLinkLeaf::generateId($stock->getOrganisationUnitId(), $stock->getSku());

//            echo "\n\nkey\n";
//            print_r($key);

            if (!isset($productLinkLeafsByOuAndSku[$key])) {
                $quantifiedStockLocations->attach($this->buildQuantifiedStockLocation($stockLocation));

//                echo "\n\nQTY STOCK LOCATION\n";
//                print_r($quantifiedStockLocations);
//
//                echo "\nATTACH CONTINUE \n";
                continue;
            }

//            print_r($linkedStockLocationsByLocationOuAndSku);

            $linkedStockLocations = new Collection(StockLocation::class, __FUNCTION__);
            foreach (array_keys($productLinkLeafsByOuAndSku[$key]->getStockSkuMap()) as $stockSku) {
                $stockLocationKey = ProductLinkLeaf::generateId($productLinkLeafsByOuAndSku[$key]->getOrganisationUnitId(), $stockSku);
//                echo "\n\nstockLocationKey\n\n";
//                print_r($stockLocationKey);

                if (isset($linkedStockLocationsByLocationOuAndSku[$stockLocationKey])) {
//                    echo "\nlinkedStockLocationsByLocationOuAndSku\n";
//
//                    print_r($linkedStockLocationsByLocationOuAndSku[$stockLocationKey]);

                    $linkedStockLocations->attach($linkedStockLocationsByLocationOuAndSku[$stockLocationKey]);
                }
            }

//            echo "KEY\n";
//            print_r($key);
//            echo "LEAF\n";
//            print_r($productLinkLeafsByOuAndSku[$key]);
//            echo "STOCK lOCA\n";
//            print_r($stockLocation);
//            echo "LINKED SL\n";
//            print_r($linkedStockLocations);

            $quantifiedStockLocations->attach(
                $this->getLinkedStockLocation(
                    $stockLocation,
                    $productLinkLeafsByOuAndSku[$key],
                    $linkedStockLocations
                )
            );

//            echo "QSL LAST ONE\n";
//            print_r($quantifiedStockLocations);
        }

//        echo "\n\nQTY STOCK LOCATION\n";
//        print_r($quantifiedStockLocations);

        return $quantifiedStockLocations;
    }

    protected function getProductLinkLeafs(StockCollection $stockCollection, Collection $stockLocations): ProductLinkLeafs
    {
        $ouSkuList = [];

        /** @var StockLocation $stockLocation */
        foreach ($stockLocations as $stockLocation) {
            $stock = $stockCollection->getById($stockLocation->getStockId());
            if ($stock instanceof Stock) {
                $ouSkuList[] = ProductLinkLeaf::generateId($stock->getOrganisationUnitId(), $stock->getSku());
            }
        }

        if (empty($ouSkuList)) {
            throw new NotFound(
                sprintf('No stock locations of type %s to lookup product links for', TypedEntity::TYPE_LINKED)
            );
        }

        return $this->productLinkLeafStorage->fetchCollectionByFilter(
            (new ProductLinkLeafFilter('all', 1))->setOuIdProductSku($ouSkuList)
        );
    }

    protected function getLinkedStockLocationsFilter(
        Collection $stockLocations,
        ProductLinkLeafs $productLinkLeafs
    ): Filter {
        $ouSkuMap = [];

        /** @var ProductLinkLeaf $productLinkLeaf */
        foreach ($productLinkLeafs as $productLinkLeaf) {
            $ouSkuMap = array_merge($ouSkuMap, array_map(function($sku) use($productLinkLeaf) {
                return ProductLinkLeaf::generateId($productLinkLeaf->getOrganisationUnitId(), $sku);
            }, array_keys($productLinkLeaf->getStockSkuMap())));
        }

        $ouSkuMap = array_unique($ouSkuMap);
        if (empty($ouSkuMap)) {
            throw new NotFound('No linked skus');
        }

        return (new Filter('all', 1))->setLocationId($stockLocations->getArrayOf('locationId'))->setOuIdSku($ouSkuMap);
    }

    /**
     * @return ProductLinkLeaf[]
     */
    protected function keyProductLinkLeafsByOuAndSku(ProductLinkLeafs $productLinkLeafs): array
    {
        $keydArray = [];

        /** @var ProductLinkLeaf $productLinkLeaf */
        foreach ($productLinkLeafs as $productLinkLeaf) {
            $key = ProductLinkLeaf::generateId($productLinkLeaf->getOrganisationUnitId(),$productLinkLeaf->getProductSku());
            $keydArray[$key] = $productLinkLeaf;
        }

        return $keydArray;
    }

    /**
     * @return StockLocation[]
     */
    protected function keyLinkedStockLocationsByLocationOuAndSku(
        StockCollection $stockCollection,
        Collection $linkedStockLocations
    ): array {
        /** @var StockLocation[] $keydLinkedStockLocations */
        $keydLinkedStockLocations = [];

        echo __METHOD__."\n";

        /** @var StockLocation $linkedStockLocation */
        foreach ($linkedStockLocations as $linkedStockLocation) {

            echo "linkedStockLocation\n";
            print_r($linkedStockLocation);

            $stock = $stockCollection->getById($linkedStockLocation->getStockId());
            if (!($stock instanceof Stock)) {
                continue;
            }

            $key = ProductLinkLeaf::generateId($stock->getOrganisationUnitId(), $stock->getSku());

            $key = $linkedStockLocation->getLocationId()."-".$stock->getSku();

            echo "KEY\n";
            print_r($key);

            $keydLinkedStockLocations[$key] = $linkedStockLocation;
        }

        return $keydLinkedStockLocations;
    }

    protected function buildQuantifiedLinkedStockLocations(
        StockLocation $stockLocation,
        ProductLinkLeaf $productLinkLeaf,
        Collection $linkedStockLocations
    ): LinkedCollection {

        echo __METHOD__ . "\n";

//        echo "\nLINKED STOCK LOCATIONS\n";
//        print_r($linkedStockLocations);


        $skuQtyMap = $productLinkLeaf->getStockSkuMap();

        $skuMap = [];
        foreach (array_keys($skuQtyMap) as $sku) {
            $skuMap[strtolower($sku)] = $sku;
        }

        $quantifiedLinkedStockLocations = new LinkedCollection(
            StockLocation::class,
            __FUNCTION__,
            [
                'stockLocation' => $stockLocation->getId(),
                'productLinkLeaf' => $productLinkLeaf->getId(),
                'linkedStockLocations' => $linkedStockLocations->getIds(),
            ]
        );

        /** @var StockCollection $stockCollection */
        $stockCollection = $this->stockStorage->fetchCollectionByFilter(
            (new StockFilter('all', 1))->setId($linkedStockLocations->getArrayOf('stockId'))
        );



        /** @var StockLocation $linkedStockLocation */
        foreach ($linkedStockLocations as $linkedStockLocation) {
            $stock = $stockCollection->getById($linkedStockLocation->getStockId());
            if (!($stock instanceof Stock)) {
                continue;
            }



            $sku = $stock->getSku();
            if (!isset($skuMap[strtolower($sku)])) {
                continue;
            }

            $quantifiedLinkedStockLocations->attach(
                $this->buildQuantifiedStockLocation(
                    $linkedStockLocation,
                    $skuQtyMap[$skuMap[strtolower($sku)]]
                )
            );

//            echo "QLINKSL\n";
//            print_r($quantifiedLinkedStockLocations);

            unset($skuQtyMap[$skuMap[strtolower($sku)]], $skuMap[strtolower($sku)]);
        }

//        echo "MISSING SKU\n";
//        print_r($skuQtyMap);

        $quantifiedLinkedStockLocations->setMissingSkus(array_keys($skuQtyMap));
        $this->attachMissingQuantifiedStockLocations($stockLocation, $quantifiedLinkedStockLocations, $skuQtyMap);


//        echo "\n\n================================== END ".__METHOD__."\n";

        return $quantifiedLinkedStockLocations;
    }

    protected function buildQuantifiedStockLocation(
        StockLocation $stockLocation,
        $qty = 1,
        $zeroStock = false
    ): QuantifiedLocation {
        return (new QuantifiedLocation(
            $stockLocation->getStockId(),
            $stockLocation->getLocationId(),
            !$zeroStock ? $stockLocation->getOnHand() : 0,
            !$zeroStock ? $stockLocation->getAllocated() : 0
        ))->setComponentMultiplier($qty);
    }

    protected function buildQuantifiedStockLocations(Collection $stockLocations): Collection
    {
        $quantifiedStockLocations = new Collection(
            StockLocation::class,
            $stockLocations->getSourceDescription(),
            $stockLocations->getSourceFilters()
        );
        $quantifiedStockLocations->setTotal($stockLocations->getTotal());

        /** @var StockLocation $stockLocation */
        foreach ($stockLocations as $stockLocation) {
            $quantifiedStockLocations->attach(
                $this->buildQuantifiedStockLocation($stockLocation)
            );
        }

        return $quantifiedStockLocations;
    }

    protected function attachMissingQuantifiedStockLocations(
        StockLocation $stockLocation,
        Collection $quantifiedLinkedStockLocations,
        array $missingSkuQtyMap
    ) {
        foreach ($missingSkuQtyMap as $sku => $qty) {
            $quantifiedLinkedStockLocations->attach(
                $this->buildQuantifiedStockLocation($stockLocation, $qty, true)
            );
        }
    }
}
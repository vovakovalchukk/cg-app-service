<?php
namespace CG\Stock\Location\Storage;

use CG\FeatureFlags\Feature;
use CG\FeatureFlags\Lookup\Service as FeatureFlagsService;
use CG\Product\LinkLeaf\Collection as ProductLinkLeafs;
use CG\Product\LinkLeaf\Entity as ProductLinkLeaf;
use CG\Product\LinkLeaf\Filter as ProductLinkLeafFilter;
use CG\Product\LinkLeaf\StorageInterface as ProductLinkLeafStorage;
use CG\Stdlib\CollectionInterface;
use CG\Stdlib\Exception\Runtime\MixedResultsException;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Collection as StockCollection;
use CG\Stock\Entity as Stock;
use CG\Stock\Filter as StockFilter;
use CG\Stock\Location\Collection;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\Filter;
use CG\Stock\Location\LinkedLocation;
use CG\Stock\Location\QuantifiedLocation;
use CG\Stock\Location\RecursionException;
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
    /** @var FeatureFlagsService $featureFlagsService */
    protected $featureFlagsService;

    public function __construct(
        StorageInterface $locationStorage,
        StockStorage $stockStorage,
        ProductLinkLeafStorage $productLinkLeafStorage,
        FeatureFlagsService $featureFlagsService
    ) {
        $this->locationStorage = $locationStorage;
        $this->stockStorage = $stockStorage;
        $this->productLinkLeafStorage = $productLinkLeafStorage;
        $this->featureFlagsService = $featureFlagsService;
    }

    public function fetch($id)
    {
        return $this->getQuantifiedStockLocation(
            $this->locationStorage->fetch($id)
        );
    }

    /**
     * @param StockLocation $entity
     */
    public function remove($entity)
    {
        $this->locationStorage->remove($entity);
    }

    /**
     * @param StockLocation $entity
     */
    public function save($entity)
    {
        return $this->getQuantifiedStockLocation($this->locationStorage->save($entity));
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
        return $this->getQuantifiedStockLocations(
            $this->locationStorage->fetchCollectionByFilter($filter)
        );
    }

    protected function getQuantifiedStockLocation(StockLocation $stockLocation): QuantifiedLocation
    {
        try {
            $productLinkLeaf = $this->getProductLinkLeaf($stockLocation);
            return $this->getLinkedStockLocation($stockLocation, $productLinkLeaf);
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
        if (!$this->featureFlagsService->featureEnabledForEntity(Feature::LINKED_PRODUCTS, $stock)) {
            throw new NotFound(sprintf('Product links are disabled for ou %d', $stock->getOrganisationUnitId()));
        }
        return $this->productLinkLeafStorage->fetch(ProductLinkLeaf::generateId($stock->getOrganisationUnitId(), $stock->getSku()));
    }

    protected function getLinkedStockLocation(
        StockLocation $stockLocation,
        ProductLinkLeaf $productLinkLeaf
    ): LinkedLocation {
        try {
            $ouSkuMap = [];
            foreach ($productLinkLeaf->getStockSkuMap() as $sku => $qty) {
                $ouSkuMap[] = ProductLinkLeaf::generateId($productLinkLeaf->getOrganisationUnitId(), $sku);
            }

            if (empty($ouSkuMap)) {
                throw new NotFound('No linked skus');
            }

            $linkedStockLocations = $this->locationStorage->fetchCollectionByFilter(
                (new Filter('all', 1))->setLocationId([$stockLocation->getLocationId()])->setOuIdSku($ouSkuMap)
            );
        } catch (NotFound $exception) {
            $linkedStockLocations = new Collection(StockLocation::class, __FUNCTION__, ['id' => [$stockLocation->getId()]]);
        }

        return new LinkedLocation(
            $stockLocation->getId(),
            $stockLocation->getStockId(),
            $stockLocation->getLocationId(),
            $this->buildQuantifiedLinkedStockLocations($stockLocation, $productLinkLeaf, $linkedStockLocations)
        );
    }

    protected function getQuantifiedStockLocations(
        Collection $stockLocations,
        array $skuQtyMap = [],
        array $ids = []
    ): Collection {
        try {
            /** @var StockCollection $stockCollection */
            $stockCollection = $this->stockStorage->fetchCollectionByFilter(
                (new StockFilter('all', 1))->setId($stockLocations->getArrayOf('stockId'))
            );
        } catch (NotFound $exception) {
            return $stockLocations;
        }

        try {
            $productLinkLeafss = $this->getProductLinkLeafs($stockCollection, $stockLocations);
        } catch (NotFound $exception) {
            return $this->buildQuantifiedStockLocations($stockCollection, $stockLocations, $skuQtyMap);
        }

        try {
            /** @var Collection $linkedStockLocations */
            $linkedStockLocations = $this->locationStorage->fetchCollectionByFilter(
                $this->getLinkedStockLocationsFilter($stockLocations, $productLinkLeafss)
            );
            /** @var StockCollection $linkedStock */
            $linkedStock = $this->stockStorage->fetchCollectionByFilter(
                (new StockFilter('all', 1))->setId($linkedStockLocations->getArrayOf('stockId'))
            );
        } catch (NotFound $exception) {
            $linkedStockLocations = new Collection(StockLocation::class, __FUNCTION__, ['id' => $stockLocations->getIds()]);
            $linkedStock = new StockCollection(Stock::class, __FUNCTION__, ['id' => []]);
        }

        $productLinkLeafsByOuAndSku = $this->keyProductLinkLeafsByOuAndSku($productLinkLeafss);
        $linkedStockLocationsByLocationOuAndSku = $this->keyLinkedStockLocationsByLocationOuAndSku(
            $linkedStock,
            $linkedStockLocations
        );

        $quantifiedStockLocations = new Collection(
            StockLocation::class,
            $stockLocations->getSourceFilters(),
            $stockLocations->getSourceDescription()
        );
        $quantifiedStockLocations->setTotal($stockLocations->getTotal());

        /** @var StockLocation $stockLocation */
        foreach ($stockLocations as $stockLocation) {
            $stock = $stockCollection->getById($stockLocation->getStockId());
            if (!($stock instanceof Stock)) {
                continue;
            }

            $key = ProductLinkLeaf::generateId($stock->getOrganisationUnitId(), $stock->getSku());
            if (!isset($productLinkLeafsByOuAndSku[$key])) {
                $quantifiedStockLocations->attach($this->buildQuantifiedStockLocation($stockLocation));
                continue;
            }

            $quantifiedStockLocations->attach(
                $this->getLinkedStockLocations(
                    $stock,
                    $stockLocation,
                    $productLinkLeafsByOuAndSku[$key],
                    $linkedStockLocationsByLocationOuAndSku,
                    $ids
                )
            );
        }

        return $quantifiedStockLocations;
    }

    protected function getProductLinkLeafs(StockCollection $stockCollection, Collection $stockLocations): ProductLinkLeafs
    {
        $ouSkuMap = [];

        /** @var StockLocation $stockLocation */
        foreach ($stockLocations as $stockLocation) {
            $stock = $stockCollection->getById($stockLocation->getStockId());
            if (!($stock instanceof Stock)) {
                continue;
            }

            if (
                !$this->featureFlagsService->featureEnabledForEntity(Feature::LINKED_PRODUCTS, $stock)
                || (($stockLocation instanceof TypedEntity) && $stockLocation->getType() != TypedEntity::TYPE_LINKED)
            ) {
                continue;
            }

            $ouSkuMap[] = ProductLinkLeaf::generateId($stock->getOrganisationUnitId(), $stock->getSku());
        }

        if (empty($ouSkuMap)) {
            throw new NotFound(
                sprintf('No stock locations of type %s to lookup product links for', TypedEntity::TYPE_LINKED)
            );
        }

        return $this->productLinkLeafStorage->fetchCollectionByFilter(
            (new ProductLinkLeafFilter('all', 1))->setOuIdProductSku($ouSkuMap)
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

        if (empty($ouSkuMap)) {
            throw new NotFound('No linked skus');
        }

        return (new Filter('all', 1))->setLocationId($stockLocations->getArrayOf('locationId'))->setOuIdSku($ouSkuMap);
    }

    /**
     * @param StockLocation[] $linkedStockLocationsByLocationOuAndSku
     */
    protected function getLinkedStockLocations(
        Stock $stock,
        StockLocation $stockLocation,
        ProductLinkLeaf $productLinkLeaf,
        array $linkedStockLocationsByLocationOuAndSku,
        array $stockLocationsIds
    ): LinkedLocation {
        $productLinkLeafStockLocations = $this->getProductLinkLeafStockLocations(
            $stock,
            $stockLocation,
            $productLinkLeaf,
            $linkedStockLocationsByLocationOuAndSku,
            $stockLocationsIds
        );

        $productQuantifiedStockLocations = $this->getQuantifiedStockLocations(
            $productLinkLeafStockLocations,
            $productLinkLeaf->getStockSkuMap(),
            $stockLocationsIds
        );

        $caseInsensitiveComparison = function($sku1, $sku2) {
            return strcasecmp($sku1, $sku2);
        };

        $this->attachMissingQuantifiedStockLocations(
            $stockLocation,
            $productQuantifiedStockLocations,
            array_diff_ukey(
                $productLinkLeaf->getStockSkuMap(),
                array_intersect_ukey(
                    array_fill_keys(array_values($stockLocationsIds), true),
                    $productLinkLeaf->getStockSkuMap(),
                    $caseInsensitiveComparison
                ),
                $caseInsensitiveComparison
            )
        );

        return new LinkedLocation(
            $stockLocation->getId(),
            $stockLocation->getStockId(),
            $stockLocation->getLocationId(),
            $productQuantifiedStockLocations
        );
    }

    /**
     * @return Collection
     */
    protected function getProductLinkLeafStockLocations(
        Stock $stock,
        StockLocation $stockLocation,
        ProductLinkLeaf $productLinkLeaf,
        array $linkedStockLocationsByLocationOuAndSku,
        array &$stockLocationsIds
    ): Collection {
        $productLinkLeafStockLocations = new Collection(StockLocation::class, __FUNCTION__, ['id' => [$stockLocation->getId()]]);
        foreach ($productLinkLeaf->getStockSkuMap() as $sku => $qty) {
            $productLinkLeafStockLocationKey = $this->generateStockLocationKey($stock, $stockLocation, $sku);
            if (!isset($linkedStockLocationsByLocationOuAndSku[$productLinkLeafStockLocationKey])) {
                continue;
            }

            $productLinkLeafStockLocation = $linkedStockLocationsByLocationOuAndSku[$productLinkLeafStockLocationKey];
            try {
                if (isset($stockLocationsIds[$productLinkLeafStockLocation->getId()])) {
                    throw new RecursionException(
                        sprintf(
                            'Already fetched stock location with id %s for linked stock location %d',
                            $productLinkLeafStockLocation->getId(),
                            $stockLocation->getId()
                        )
                    );
                }
                $stockLocationsIds[$productLinkLeafStockLocation->getId()] = $sku;
                $productLinkLeafStockLocations->attach($productLinkLeafStockLocation);
            } catch (RecursionException $exception) {
                $this->logCriticalException($exception, static::LOG_MSG_RECURSIVE_FETCH, ['id' => $productLinkLeafStockLocation->getId(), 'sku' => $productLinkLeafStockLocation->getSku()], static::LOG_CODE_RECURSIVE_FETCH, ['ou' => $productLinkLeafStockLocation->getOrganisationUnitId()]);
            }
        }
        return $productLinkLeafStockLocations;
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

    protected function generateStockLocationKey(Stock $stock, StockLocation $stockLocation, $sku = null): string
    {
        return implode(
            '-',
            [
                $stockLocation->getLocationId(),
                $stock->getOrganisationUnitId(),
                strtolower($sku ?: $stock->getSku())
            ]
        );
    }

    /**
     * @return StockLocation[]
     */
    protected function keyLinkedStockLocationsByLocationOuAndSku(
        StockCollection $stockCollection,
        Collection $linkedStockLocations
    ): array {
        $keydArray = [];

        /** @var StockLocation $linkedStockLocation */
        foreach ($linkedStockLocations as $linkedStockLocation) {
            $stock = $stockCollection->getById($linkedStockLocation->getStockId());
            if (!($stock instanceof Stock)) {
                continue;
            }

            $key = $this->generateStockLocationKey($stock, $linkedStockLocation);
            $keydArray[$key] = $linkedStockLocation;
        }

        return $keydArray;
    }

    protected function buildQuantifiedLinkedStockLocations(
        StockLocation $stockLocation,
        ProductLinkLeaf $productLinkLeaf,
        Collection $linkedStockLocations
    ): Collection {
        $skuQtyMap = $productLinkLeaf->getStockSkuMap();

        $skuMap = [];
        foreach (array_keys($skuQtyMap) as $sku) {
            $skuMap[strtolower($sku)] = $sku;
        }

        $quantifiedLinkedStockLocations = new Collection(
            StockLocation::class,
            __FUNCTION__,
            compact('stockLocation', 'productLinkLeaf', 'linkedStockLocations')
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

            unset($skuQtyMap[$skuMap[strtolower($sku)]], $skuMap[strtolower($sku)]);
        }

        $this->attachMissingQuantifiedStockLocations($stockLocation, $quantifiedLinkedStockLocations, $skuQtyMap);
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
        ))->setQuantifier($qty);
    }

    protected function buildQuantifiedStockLocations(
        StockCollection $stockCollection,
        Collection $stockLocations,
        array $skuQtyMap = []
    ): Collection {
        $skuMap = [];
        foreach (array_keys($skuQtyMap) as $sku) {
            $skuMap[strtolower($sku)] = $sku;
        }

        $quantifiedStockLocations = new Collection(
            StockLocation::class,
            $stockLocations->getSourceDescription(),
            $stockLocations->getSourceFilters()
        );
        $quantifiedStockLocations->setTotal($stockLocations->getTotal());

        /** @var StockLocation $stockLocation */
        foreach ($stockLocations as $stockLocation) {
            $stock = $stockCollection->getById($stockLocation->getStockId());
            if (!($stock instanceof Stock)) {
                continue;
            }

            $sku = $stock->getSku();
            $qty = isset($skuMap[strtolower($sku)], $skuQtyMap[$skuMap[strtolower($sku)]]) ? $skuQtyMap[$skuMap[strtolower($sku)]] : 1;
            $quantifiedStockLocations->attach(
                $this->buildQuantifiedStockLocation(
                    $stockLocation,
                    $qty
                )
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
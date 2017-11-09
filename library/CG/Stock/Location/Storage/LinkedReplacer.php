<?php
namespace CG\Stock\Location\Storage;

use CG\FeatureFlags\Feature;
use CG\FeatureFlags\Lookup\Service as FeatureFlagsService;
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
        $quantifiedEntity = $this->getQuantifiedStockLocation($entity, $missingStockLocationSkus);
        if (!($quantifiedEntity instanceof LinkedLocation)) {
            return $this->locationStorage->save($quantifiedEntity);
        }

        if (!empty($missingStockLocationSkus)) {
            throw (new ValidationMessagesException(StatusCode::UNPROCESSABLE_ENTITY))->addErrorWithField(
                'stockSku',
                sprintf(
                    'You can not update linked stock location %s because the following stock location sku(s) are missing: %s',
                    $entity->getId(),
                    implode(', ', $missingStockLocationSkus)
                )
            );
        }

        try {
            $fetchedEntity = $this->fetch($entity->getId());
        } catch (NotFound $exception) {
            $fetchedEntity = $this->getQuantifiedStockLocation($entity);
        }

        if (empty($difference = $this->calculateDifference($fetchedEntity, $entity))) {
            return $quantifiedEntity;
        }

        /** @var StockLocation $linkedLocation */
        foreach ($quantifiedEntity->getLinkedLocations() as $linkedLocation) {
            $this->applyDifference($linkedLocation, $difference);
            $this->locationStorage->save($linkedLocation);
        }

        return $quantifiedEntity;
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
        $quantifier = ($stockLocation instanceof QuantifiedLocation) ? $stockLocation->getQuantifier() : 1;
        foreach ($difference as $stock => $stockDifference) {
            $currentStock = $stockLocation->{'get' . $stock}(false);
            $stockLocation->{'set' . $stock}($currentStock + ($quantifier * $stockDifference), false);
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
        return $this->getQuantifiedStockLocations(
            $this->locationStorage->fetchCollectionByFilter($filter)
        );
    }

    protected function getQuantifiedStockLocation(
        StockLocation $stockLocation,
        &$missingStockLocationSkus = null
    ): QuantifiedLocation {
        try {
            $productLinkLeaf = $this->getProductLinkLeaf($stockLocation);
            return $this->getLinkedStockLocation($stockLocation, $productLinkLeaf, $missingStockLocationSkus);
        } catch (NotFound $exception) {
            $missingStockLocationSkus = [];
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
        ProductLinkLeaf $productLinkLeaf,
        &$missingStockLocationSkus = null
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
            $this->buildQuantifiedLinkedStockLocations(
                $stockLocation,
                $productLinkLeaf,
                $linkedStockLocations,
                $missingStockLocationSkus
            )
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
        $productLinkLeafedStockLocations = $this->buildProductLinkLeafedStockLocations(
            $stock,
            $stockLocation,
            $productLinkLeaf,
            $linkedStockLocationsByLocationOuAndSku,
            $stockLocationsIds
        );

        $productQuantifiedStockLocations = $this->getQuantifiedStockLocations(
            $productLinkLeafedStockLocations,
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
    protected function buildProductLinkLeafedStockLocations(
        Stock $stock,
        StockLocation $stockLocation,
        ProductLinkLeaf $productLinkLeaf,
        array $linkedStockLocationsByLocationOuAndSku,
        array &$stockLocationsIds
    ): Collection {
        $productLinkLeafedStockLocations = new Collection(StockLocation::class, __FUNCTION__, ['id' => [$stockLocation->getId()]]);

        foreach ($productLinkLeaf->getStockSkuMap() as $sku => $qty) {
            $productLinkLeafedLocationKey = $this->generateStockLocationKey($stock, $stockLocation, $sku);
            if (!isset($linkedStockLocationsByLocationOuAndSku[$productLinkLeafedLocationKey])) {
                continue;
            }

            $productLinkLeafedStockLocation = $linkedStockLocationsByLocationOuAndSku[$productLinkLeafedLocationKey];
            try {
                if (isset($stockLocationsIds[$productLinkLeafedStockLocation->getId()])) {
                    throw new RecursionException(
                        sprintf(
                            'Already fetched stock location with id %s for linked stock location %d',
                            $productLinkLeafedStockLocation->getId(),
                            $stockLocation->getId()
                        )
                    );
                }
                $stockLocationsIds[$productLinkLeafedStockLocation->getId()] = $sku;
                $productLinkLeafedStockLocations->attach($productLinkLeafedStockLocation);
            } catch (RecursionException $exception) {
                $this->logCriticalException($exception, static::LOG_MSG_RECURSIVE_FETCH, ['id' => $productLinkLeafedStockLocation->getId(), 'sku' => $productLinkLeafedStockLocation->getSku()], static::LOG_CODE_RECURSIVE_FETCH, ['ou' => $productLinkLeafedStockLocation->getOrganisationUnitId()]);
            }
        }

        return $productLinkLeafedStockLocations;
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
        Collection $linkedStockLocations,
        &$missingStockLocationSkus = null
    ): Collection {
        $skuQtyMap = $productLinkLeaf->getStockSkuMap();

        $skuMap = [];
        foreach (array_keys($skuQtyMap) as $sku) {
            $skuMap[strtolower($sku)] = $sku;
        }

        $quantifiedLinkedStockLocations = new Collection(
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

            unset($skuQtyMap[$skuMap[strtolower($sku)]], $skuMap[strtolower($sku)]);
        }

        $missingStockLocationSkus = array_keys($skuQtyMap);
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
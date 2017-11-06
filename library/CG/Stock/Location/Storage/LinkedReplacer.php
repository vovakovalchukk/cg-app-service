<?php
namespace CG\Stock\Location\Storage;

use CG\FeatureFlags\Feature;
use CG\FeatureFlags\Lookup\Service as FeatureFlagsService;
use CG\Product\Link\Collection as ProductLinks;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Link\Filter as ProductLinkFilter;
use CG\Product\Link\StorageInterface as ProductLinkStorage;
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

    const LOG_CODE_RECURSIVE_SAVE = 'Recursive save detected - stock location will not be updated again';
    const LOG_MSG_RECURSIVE_SAVE = 'Recursive save detected - stock location %d will not be updated again';
    const LOG_CODE_RECURSIVE_FETCH = 'Recursive fetch detected - stock location will be replaced with a zero\'d entity';
    const LOG_MSG_RECURSIVE_FETCH = 'Recursive fetch detected - stock location %d (%s) will be replaced with a zero\'d entity';

    /** @var StorageInterface $locationStorage */
    protected $locationStorage;
    /** @var StockStorage $stockStorage */
    protected $stockStorage;
    /** @var ProductLinkStorage $productLinkStorage */
    protected $productLinkStorage;
    /** @var FeatureFlagsService $featureFlagsService */
    protected $featureFlagsService;

    public function __construct(
        StorageInterface $locationStorage,
        StockStorage $stockStorage,
        ProductLinkStorage $productLinkStorage,
        FeatureFlagsService $featureFlagsService
    ) {
        $this->locationStorage = $locationStorage;
        $this->stockStorage = $stockStorage;
        $this->productLinkStorage = $productLinkStorage;
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
    public function save($entity, array $ids = [])
    {
        if (isset($ids[$entity->getId()])) {
            throw new RecursionException(sprintf('Already saved stock location with id %s', $entity->getId()));
        }

        $ids[$entity->getId()] = true;
        $location = $this->getQuantifiedStockLocation($this->locationStorage->save($entity));
        if ($location instanceof LinkedLocation) {
            /** @var StockLocation $linkedLocation */
            foreach ($location->getLinkedLocations() as $linkedLocation) {
                try {
                    $this->save($linkedLocation, $ids);
                } catch (RecursionException $exception) {
                    $this->logCriticalException($exception, static::LOG_MSG_RECURSIVE_SAVE, ['id' => $linkedLocation->getId()], static::LOG_CODE_RECURSIVE_SAVE);
                }
            }
        }
        return $location;
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

    /**
     * @return QuantifiedLocation
     */
    protected function getQuantifiedStockLocation(StockLocation $stockLocation)
    {
        try {
            $productLink = $this->getProductLink($stockLocation);
            return $this->getLinkedStockLocation($stockLocation, $productLink);
        } catch (NotFound $exception) {
            return $this->buildQuantifiedStockLocation($stockLocation);
        }
    }

    /**
     * @return ProductLink
     */
    protected function getProductLink(StockLocation $stockLocation)
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
        return $this->productLinkStorage->fetch($stock->getOrganisationUnitId() . '-' . $stock->getSku());
    }

    /**
     * @return LinkedLocation
     */
    protected function getLinkedStockLocation(StockLocation $stockLocation, ProductLink $productLink)
    {
        try {
            $ouSkuMap = [];
            foreach ($productLink->getStockSkuMap() as $sku => $qty) {
                $ouSkuMap[] = $productLink->getOrganisationUnitId() . '-' . $sku;
            }

            if (empty($ouSkuMap)) {
                throw new NotFound('No linked skus');
            }

            $linkedStockLocations = $this->fetchCollectionByFilter(
                (new Filter('all', 1))->setLocationId([$stockLocation->getLocationId()])->setOuIdSku($ouSkuMap)
            );
        } catch (NotFound $exception) {
            $linkedStockLocations = new Collection(StockLocation::class, __FUNCTION__, ['id' => [$stockLocation->getId()]]);
        }

        return new LinkedLocation(
            $stockLocation->getId(),
            $stockLocation->getStockId(),
            $stockLocation->getLocationId(),
            $this->buildQuantifiedLinkedStockLocations($stockLocation, $productLink, $linkedStockLocations)
        );
    }

    /**
     * @return Collection
     */
    protected function getQuantifiedStockLocations(Collection $stockLocations, array $skuQtyMap = [], array $ids = [])
    {
        try {
            /** @var StockCollection $stockCollection */
            $stockCollection = $this->stockStorage->fetchCollectionByFilter(
                (new StockFilter('all', 1))->setId($stockLocations->getArrayOf('stockId'))
            );
        } catch (NotFound $exception) {
            return $stockLocations;
        }

        try {
            $productLinks = $this->getProductLinks($stockCollection, $stockLocations);
        } catch (NotFound $exception) {
            return $this->buildQuantifiedStockLocations($stockCollection, $stockLocations, $skuQtyMap);
        }

        try {
            $linkedStockLocations = $this->locationStorage->fetchCollectionByFilter(
                $this->getLinkedStockLocationsFilter($stockLocations, $productLinks)
            );
            /** @var StockCollection $linkedStock */
            $linkedStock = $this->stockStorage->fetchCollectionByFilter(
                (new StockFilter('all', 1))->setId($linkedStockLocations->getArrayOf('stockId'))
            );
        } catch (NotFound $exception) {
            $linkedStockLocations = new Collection(StockLocation::class, __FUNCTION__, ['id' => $stockLocations->getIds()]);
            $linkedStock = new StockCollection(Stock::class, __FUNCTION__, ['id' => []]);
        }

        $productLinksByOuAndSku = $this->keyProductLinksByOuAndSku($productLinks);
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

            $key = $stock->getOrganisationUnitId() . '-' . strtolower($stock->getSku());
            if (!isset($productLinksByOuAndSku[$key])) {
                $quantifiedStockLocations->attach($this->buildQuantifiedStockLocation($stockLocation));
                continue;
            }

            $quantifiedStockLocations->attach(
                $this->getLinkedStockLocations(
                    $stock,
                    $stockLocation,
                    $productLinksByOuAndSku[$key],
                    $linkedStockLocationsByLocationOuAndSku,
                    $ids
                )
            );
        }

        return $quantifiedStockLocations;
    }

    /**
     * @return ProductLinks
     */
    protected function getProductLinks(StockCollection $stockCollection, Collection $stockLocations)
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

            $ouSkuMap[] = $stock->getOrganisationUnitId() . '-' . $stock->getSku();
        }

        if (empty($ouSkuMap)) {
            throw new NotFound(
                sprintf('No stock locations of type %s to lookup product links for', TypedEntity::TYPE_LINKED)
            );
        }

        return $this->productLinkStorage->fetchCollectionByFilter(
            (new ProductLinkFilter('all', 1))->setOuIdProductSku($ouSkuMap)
        );
    }

    /**
     * @return Filter
     */
    protected function getLinkedStockLocationsFilter(Collection $stockLocations, ProductLinks $productLinks)
    {
        $ouSkuMap = [];

        /** @var ProductLink $productLink */
        foreach ($productLinks as $productLink) {
            $ouSkuMap = array_merge($ouSkuMap, array_map(function($sku) use($productLink) {
                return $productLink->getOrganisationUnitId() . '-' . $sku;
            }, array_keys($productLink->getStockSkuMap())));
        }

        if (empty($ouSkuMap)) {
            throw new NotFound('No linked skus');
        }

        return (new Filter('all', 1))->setLocationId($stockLocations->getArrayOf('locationId'))->setOuIdSku($ouSkuMap);
    }

    /**
     * @param StockLocation[] $linkedStockLocationsByLocationOuAndSku
     * @return LinkedLocation
     */
    protected function getLinkedStockLocations(
        Stock $stock,
        StockLocation $stockLocation,
        ProductLink $productLink,
        array $linkedStockLocationsByLocationOuAndSku,
        array $stockLocationsIds
    ) {
        $productLinkedStockLocations = $this->buildProductLinkedStockLocations(
            $stock,
            $stockLocation,
            $productLink,
            $linkedStockLocationsByLocationOuAndSku,
            $stockLocationsIds
        );

        $productQuantifiedStockLocations = $this->getQuantifiedStockLocations(
            $productLinkedStockLocations,
            $productLink->getStockSkuMap(),
            $stockLocationsIds
        );

        $caseInsensitiveComparison = function($sku1, $sku2) {
            return strcasecmp($sku1, $sku2);
        };

        $this->attachMissingQuantifiedStockLocations(
            $stockLocation,
            $productQuantifiedStockLocations,
            array_diff_ukey(
                $productLink->getStockSkuMap(),
                array_intersect_ukey(
                    array_fill_keys(array_values($stockLocationsIds), true),
                    $productLink->getStockSkuMap(),
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
    protected function buildProductLinkedStockLocations(
        Stock $stock,
        StockLocation $stockLocation,
        ProductLink $productLink,
        array $linkedStockLocationsByLocationOuAndSku,
        array &$stockLocationsIds
    ) {
        $productLinkedStockLocations = new Collection(StockLocation::class, __FUNCTION__, ['id' => [$stockLocation->getId()]]);

        foreach ($productLink->getStockSkuMap() as $sku => $qty) {
            $productLinkedLocationKey = $this->generateStockLocationKey($stock, $stockLocation, $sku);
            if (!isset($linkedStockLocationsByLocationOuAndSku[$productLinkedLocationKey])) {
                continue;
            }

            $productLinkedStockLocation = $linkedStockLocationsByLocationOuAndSku[$productLinkedLocationKey];
            try {
                if (isset($stockLocationsIds[$productLinkedStockLocation->getId()])) {
                    throw new RecursionException(
                        sprintf(
                            'Already fetched stock location with id %s for linked stock location %d',
                            $productLinkedStockLocation->getId(),
                            $stockLocation->getId()
                        )
                    );
                }
                $stockLocationsIds[$productLinkedStockLocation->getId()] = $sku;
                $productLinkedStockLocations->attach($productLinkedStockLocation);
            } catch (RecursionException $exception) {
                $this->logCriticalException($exception, static::LOG_MSG_RECURSIVE_FETCH, ['id' => $productLinkedStockLocation->getId(), 'sku' => $productLinkedStockLocation->getSku()], static::LOG_CODE_RECURSIVE_FETCH, ['ou' => $productLinkedStockLocation->getOrganisationUnitId()]);
            }
        }

        return $productLinkedStockLocations;
    }

    /**
     * @return ProductLink[]
     */
    protected function keyProductLinksByOuAndSku(ProductLinks $productLinks)
    {
        $keydArray = [];

        /** @var ProductLink $productLink */
        foreach ($productLinks as $productLink) {
            $key = $productLink->getOrganisationUnitId() . '-' . strtolower($productLink->getProductSku());
            $keydArray[$key] = $productLink;
        }

        return $keydArray;
    }

    /**
     * @return string
     */
    protected function generateStockLocationKey(Stock $stock, StockLocation $stockLocation, $sku = null)
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
    ) {
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

    /**
     * @return Collection
     */
    protected function buildQuantifiedLinkedStockLocations(
        StockLocation $stockLocation,
        ProductLink $productLink,
        Collection $linkedStockLocations
    ) {
        $skuQtyMap = $productLink->getStockSkuMap();

        $skuMap = [];
        foreach (array_keys($skuQtyMap) as $sku) {
            $skuMap[strtolower($sku)] = $sku;
        }

        $quantifiedLinkedStockLocations = new Collection(
            StockLocation::class,
            __FUNCTION__,
            compact('stockLocation', 'productLink', 'linkedStockLocations')
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

    /**
     * @return QuantifiedLocation
     */
    protected function buildQuantifiedStockLocation(StockLocation $stockLocation, $qty = 1, $zeroStock = false)
    {
        return (new QuantifiedLocation(
            $stockLocation->getStockId(),
            $stockLocation->getLocationId(),
            !$zeroStock ? $stockLocation->getOnHand() : 0,
            !$zeroStock ? $stockLocation->getAllocated() : 0
        ))->setQuantifier($qty);
    }

    /**
     * @return Collection
     */
    protected function buildQuantifiedStockLocations(StockCollection $stockCollection, Collection $stockLocations, array $skuQtyMap = [])
    {
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
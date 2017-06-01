<?php
namespace CG\Stock\Location\Storage;

use CG\Product\Link\Collection as ProductLinks;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Link\Filter as ProductLinkFilter;
use CG\Product\Link\StorageInterface as ProductLinkStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Location\Collection;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\Filter;
use CG\Stock\Location\LinkedLocation;
use CG\Stock\Location\QuantifiedLocation;
use CG\Stock\Location\RecursionException;
use CG\Stock\Location\StorageInterface;
use CG\Stock\Location\TypedEntity;

class LinkedReplacer implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE_RECURSIVE_SAVE = 'Recursive save detected - stock location will not be updated again';
    const LOG_MSG_RECURSIVE_SAVE = 'Recursive save detected - stock location %d (%s) will not be updated again';
    const LOG_CODE_RECURSIVE_FETCH = 'Recursive fetch detected - stock location will be replaced with a zero\'d entity';
    const LOG_MSG_RECURSIVE_FETCH = 'Recursive fetch detected - stock location %d (%s) will be replaced with a zero\'d entity';

    /** @var StorageInterface $locationStorage */
    protected $locationStorage;
    /** @var ProductLinkStorage $productLinkStorage */
    protected $productLinkStorage;

    public function __construct(StorageInterface $locationStorage, ProductLinkStorage $productLinkStorage)
    {
        $this->locationStorage = $locationStorage;
        $this->productLinkStorage = $productLinkStorage;
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
                    $this->logCriticalException($exception, static::LOG_MSG_RECURSIVE_SAVE, ['id' => $linkedLocation->getId(), 'sku' => $linkedLocation->getSku()], static::LOG_CODE_RECURSIVE_SAVE, ['ou' => $linkedLocation->getOrganisationUnitId()]);
                }
            }
        }
        return $location;
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
        } catch (NotFound $exception) {
            return $this->buildQuantifiedStockLocation($stockLocation, $stockLocation->getSku());
        }

        $linkedStockLocations = $this->getLinkedStockLocations($stockLocation, $productLink);
        return new LinkedLocation(
            $stockLocation->getId(),
            $stockLocation->getStockId(),
            $stockLocation->getLocationId(),
            $stockLocation->getOrganisationUnitId(),
            $stockLocation->getSku(),
            $this->buildQuantifiedLinkedStockLocations($stockLocation, $productLink, $linkedStockLocations)
        );
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

        $productLink = $this->productLinkStorage->fetchCollectionByFilter(
            (new ProductLinkFilter(1, 1))
                ->setOrganisationUnitId([$stockLocation->getOrganisationUnitId()])
                ->setProductSku([$stockLocation->getSku()])
        );
        $productLink->rewind();
        return $productLink->current();
    }

    /**
     * @return Collection
     */
    protected function getLinkedStockLocations(StockLocation $stockLocation, ProductLink $productLink)
    {
        try {
            $ouSkuMap = [];
            foreach ($productLink->getStockSkuMap() as $sku => $qty) {
                $ouSkuMap[] = $stockLocation->getOrganisationUnitId() . '-' . $sku;
            }

            if (empty($ouSkuMap)) {
                throw new NotFound('No linked skus');
            }

            return $this->fetchCollectionByFilter(
                (new Filter('all', 1))->setLocationId([$stockLocation->getLocationId()])->setOuIdSku($ouSkuMap)
            );
        } catch (NotFound $exception) {
            return new Collection(StockLocation::class, __FUNCTION__, ['id' => [$stockLocation->getId()]]);
        }
    }

    /**
     * @return Collection
     */
    protected function getQuantifiedStockLocations(Collection $stockLocations, array $skuQtyMap = [], array $ids = [])
    {
        try {
            $productLinks = $this->getProductLinks($stockLocations);
        } catch (NotFound $exception) {
            return $this->buildQuantifiedStockLocations($stockLocations, $skuQtyMap);
        }

        try {
            $linkedStockLocations = $this->locationStorage->fetchCollectionByFilter(
                $this->getLinkedStockLocationsFilter($stockLocations, $productLinks)
            );
        } catch (NotFound $exception) {
            $linkedStockLocations = new Collection(StockLocation::class, __FUNCTION__, ['id' => $stockLocations->getIds()]);
        }

        $productLinksByOuAndSku = $this->keyProductLinksByOuAndSku($productLinks);
        $linkedStockLocationsByLocationOuAndSku = $this->keyLinkedStockLocationsByLocationOuAndSku($linkedStockLocations);

        $quantifiedStockLocations = new Collection(
            StockLocation::class,
            $stockLocations->getSourceFilters(),
            $stockLocations->getSourceDescription()
        );
        $quantifiedStockLocations->setTotal($stockLocations->getTotal());

        /** @var StockLocation $stockLocation */
        foreach ($stockLocations as $stockLocation) {
            $key = $stockLocation->getOrganisationUnitId() . '-' . strtolower($stockLocation->getSku());
            if (!isset($productLinksByOuAndSku[$key])) {
                $quantifiedStockLocations->attach($this->buildQuantifiedStockLocation($stockLocation, $stockLocation->getSku()));
                continue;
            }

            $productLink = $productLinksByOuAndSku[$key];
            $productLinkedStockLocations = new Collection(StockLocation::class, __FUNCTION__, ['id' => [$stockLocation->getId()]]);

            $stockLocationsIds = $ids;
            foreach ($productLink->getStockSkuMap() as $sku => $qty) {
                $productLinkedLocationKey = $this->generateStockLocationKey($stockLocation, $sku);
                if (!isset($linkedStockLocationsByLocationOuAndSku[$productLinkedLocationKey])) {
                    continue;
                }

                /** @var StockLocation $productLinkedStockLocation */
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
                    $stockLocationsIds[$productLinkedStockLocation->getId()] = true;
                    $productLinkedStockLocations->attach($productLinkedStockLocation);
                } catch (RecursionException $exception) {
                    $this->logCriticalException($exception, static::LOG_MSG_RECURSIVE_FETCH, ['id' => $productLinkedStockLocation->getId(), 'sku' => $productLinkedStockLocation->getSku()], static::LOG_CODE_RECURSIVE_FETCH, ['ou' => $productLinkedStockLocation->getOrganisationUnitId()]);
                }
            }

            $missingSkuMap = array_diff_ukey(
                $productLink->getStockSkuMap(),
                array_fill_keys($productLinkedStockLocations->getArrayOf('sku'), true),
                function($productLinkSku, $locationSku) {
                    return strcasecmp($productLinkSku, $locationSku);
                }
            );

            $productQuantifiedStockLocations = $this->getQuantifiedStockLocations(
                $productLinkedStockLocations,
                $productLink->getStockSkuMap(),
                $stockLocationsIds
            );

            foreach ($missingSkuMap as $sku => $qty) {
                $productQuantifiedStockLocations->attach(
                    $this->buildQuantifiedStockLocation($stockLocation, $sku, $qty, true)
                );
            }

            $quantifiedStockLocations->attach(
                new LinkedLocation(
                    $stockLocation->getId(),
                    $stockLocation->getStockId(),
                    $stockLocation->getLocationId(),
                    $stockLocation->getOrganisationUnitId(),
                    $stockLocation->getSku(),
                    $productQuantifiedStockLocations
                )
            );
        }

        return $quantifiedStockLocations;
    }

    /**
     * @return ProductLinks
     */
    protected function getProductLinks(Collection $stockLocations)
    {
        $ouSkuMap = [];

        /** @var StockLocation $stockLocation */
        foreach ($stockLocations as $stockLocation) {
            if (($stockLocation instanceof TypedEntity) && $stockLocation->getType() != TypedEntity::TYPE_LINKED) {
                continue;
            }
            $ouSkuMap[] = $stockLocation->getOrganisationUnitId() . '-' . $stockLocation->getSku();
        }

        if (empty($ouSkuMap)) {
            throw new NotFound(
                sprintf('No stock locations of type %s to lookup product links for', TypedEntity::TYPE_LINKED)
            );
        }

        return $this->productLinkStorage->fetchCollectionByFilter(
            (new ProductLinkFilter(1, 1))->setOuIdProductSku($ouSkuMap)
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

    protected function generateStockLocationKey(StockLocation $stockLocation, $sku = null)
    {
        return implode(
            '-',
            [
                $stockLocation->getLocationId(),
                $stockLocation->getOrganisationUnitId(),
                strtolower($sku ?: $stockLocation->getSku())
            ]
        );
    }

    /**
     * @return StockLocation[]
     */
    protected function keyLinkedStockLocationsByLocationOuAndSku(Collection $linkedStockLocations)
    {
        $keydArray = [];

        /** @var StockLocation $linkedStockLocation */
        foreach ($linkedStockLocations as $linkedStockLocation) {
            $key = $this->generateStockLocationKey($linkedStockLocation);
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

        /** @var StockLocation $linkedStockLocation */
        foreach ($linkedStockLocations as $linkedStockLocation) {
            $sku = $linkedStockLocation->getSku();
            if (!isset($skuMap[strtolower($sku)])) {
                continue;
            }

            $quantifiedLinkedStockLocations->attach(
                $this->buildQuantifiedStockLocation(
                    $linkedStockLocation,
                    $linkedStockLocation->getSku(),
                    $skuQtyMap[$skuMap[strtolower($sku)]]
                )
            );

            unset($skuQtyMap[$skuMap[strtolower($sku)]], $skuMap[strtolower($sku)]);
        }

        // TODO: Refactor
        foreach ($skuQtyMap as $sku => $qty) {
            $quantifiedLinkedStockLocations->attach(
                $this->buildQuantifiedStockLocation($stockLocation, $sku, $qty, true)
            );
        }

        return $quantifiedLinkedStockLocations;
    }

    /**
     * @return QuantifiedLocation
     */
    protected function buildQuantifiedStockLocation(StockLocation $stockLocation, $sku, $qty = 1, $zeroStock = false)
    {
        return (new QuantifiedLocation(
            $stockLocation->getStockId(),
            $stockLocation->getLocationId(),
            $stockLocation->getOrganisationUnitId(),
            $sku,
            !$zeroStock ? $stockLocation->getOnHand() : 0,
            !$zeroStock ? $stockLocation->getAllocated() : 0
        ))->setQuantifier($qty);
    }

    protected function buildQuantifiedStockLocations(Collection $stockLocations, array $skuQtyMap = [])
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
            $sku = $stockLocation->getSku();
            $qty = isset($skuMap[strtolower($sku)], $skuQtyMap[$skuMap[strtolower($sku)]]) ? $skuQtyMap[$skuMap[strtolower($sku)]] : 1;
            $quantifiedStockLocations->attach(
                $this->buildQuantifiedStockLocation(
                    $stockLocation,
                    $sku,
                    $qty
                )
            );
        }

        return $quantifiedStockLocations;
    }
}
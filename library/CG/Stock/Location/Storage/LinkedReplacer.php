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
use CG\Stock\Location\Entity as Location; // TODO: StockLocation
use CG\Stock\Location\Filter;
use CG\Stock\Location\LinkedLocation;
use CG\Stock\Location\QuantifiedLocation;
use CG\Stock\Location\RecursionException;
use CG\Stock\Location\StorageInterface;

class LinkedReplacer implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE_RECURSIVE_SAVE = 'Recursive save detected - stock location will not be updated again';
    const LOG_MSG_RECURSIVE_SAVE = 'Recursive save detected - stock location %d (%s) will not be updated again';
    const LOG_CODE_RECURSIVE_FETCH = 'Recursive fetch detected - stock location will be replaced with an zero\'d entity';
    const LOG_MSG_RECURSIVE_FETCH = 'Recursive fetch detected - stock location %d (%s) will be replaced with an zero\'d entity';

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
        return $this->getQuantifiedLocation(
            $this->locationStorage->fetch($id)
        );
    }

    /**
     * @param Location $entity
     */
    public function remove($entity)
    {
        $this->locationStorage->remove($entity);
    }

    /**
     * @param Location $entity
     */
    public function save($entity, array $ids = [])
    {
        if (isset($ids[$entity->getId()])) {
            throw new RecursionException(sprintf('Already saved stock location with id %s', $entity->getId()));
        }

        $ids[$entity->getId()] = true;
        $location = $this->getQuantifiedLocation($this->locationStorage->save($entity));
        if ($location instanceof LinkedLocation) {
            /** @var Location $linkedLocation */
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
        return $this->getQuantifiedLocations(
            $this->locationStorage->fetchCollectionByFilter($filter)
        );
    }

    /**
     * @return QuantifiedLocation
     */
    protected function getQuantifiedLocation(Location $location)
    {
        try {
            $productLink = $this->getProductLink($location);
        } catch (NotFound $exception) {
            return $this->buildQuantifiedLocation($location, $location->getSku());
        }

        try {
            $ouSkuMap = [];
            foreach ($productLink->getStockSkuMap() as $sku => $qty) {
                $ouSkuMap[] = $location->getOrganisationUnitId() . '-' . $sku;
            }

            if (empty($ouSkuMap)) {
                throw new NotFound('No linked skus');
            }

            $locationIds = [$location->getId() => true];
            $linkedLocations = $this->fetchCollectionByFilter(
                (new Filter('all', 1))->setLocationId([$location->getLocationId()])->setOuIdSku($ouSkuMap),
                $locationIds
            );
        } catch (NotFound $exception) {
            $linkedLocations = new Collection(Location::class, __FUNCTION__, compact('id'));
        }

        return new LinkedLocation(
            $location->getId(),
            $location->getStockId(),
            $location->getLocationId(),
            $location->getOrganisationUnitId(),
            $location->getSku(),
            $this->buildQuantifiedLinkedLocations($location, $productLink, $linkedLocations)
        );
    }

    /**
     * @return ProductLink
     */
    protected function getProductLink(Location $location)
    {
        $productLink = $this->productLinkStorage->fetchCollectionByFilter(
            (new ProductLinkFilter(1, 1))
                ->setOrganisationUnitId([$location->getOrganisationUnitId()])
                ->setProductSku([$location->getSku()])
        );
        $productLink->rewind();
        return $productLink->current();
    }

    /**
     * @return Collection
     */
    protected function getQuantifiedLocations(Collection $locations, array $skuQtyMap = [], array $ids = [])
    {
        try {
            $productLinks = $this->getProductLinks($locations);
        } catch (NotFound $exception) {
            return $this->buildQuantifiedLocations($locations, $skuQtyMap);
        }

        try {
            $linkedLocations = $this->locationStorage->fetchCollectionByFilter(
                $this->getLinkedLocationsFilter($locations, $productLinks)
            );
        } catch (NotFound $exception) {
            $linkedLocations = new Collection(Location::class, __FUNCTION__, ['id' => $locations->getIds()]);
        }

        $productLinksByOuAndSku = $this->keyProductLinksByOuAndSku($productLinks);
        $linkedLocationsByLocationOuAndSku = $this->keyLinkedLocationsByLocationOuAndSku($linkedLocations);

        $quantifiedLocations = new Collection(Location::class, $locations->getSourceFilters(), $locations->getSourceDescription());
        $quantifiedLocations->setTotal($locations->getTotal());

        /** @var Location $location */
        foreach ($locations as $location) {
            $key = $location->getOrganisationUnitId() . '-' . strtolower($location->getSku());
            if (!isset($productLinksByOuAndSku[$key])) {
                $quantifiedLocations->attach($this->buildQuantifiedLocation($location, $location->getSku()));
                continue;
            }

            $productLink = $productLinksByOuAndSku[$key];
            $productLinkedLocations = new Collection(Location::class, __FUNCTION__, ['id' => [$location->getId()]]);

            $locationsIds = $ids;
            foreach ($productLink->getStockSkuMap() as $sku => $qty) {
                $productLinkedLocationKey = $this->generateLocationKey($location, $sku);
                if (!isset($linkedLocationsByLocationOuAndSku[$productLinkedLocationKey])) {
                    continue;
                }

                /** @var Location $productLinkedLocation */
                $productLinkedLocation = $linkedLocationsByLocationOuAndSku[$productLinkedLocationKey];
                try {
                    if (isset($locationsIds[$productLinkedLocation->getId()])) {
                        throw new RecursionException(
                            sprintf('Already fetched stock location with id %s for linked stock location %d', $productLinkedLocation->getId(), $location->getId())
                        );
                    }
                    $locationsIds[$productLinkedLocation->getId()] = true;
                    $productLinkedLocations->attach($productLinkedLocation);
                } catch (RecursionException $exception) {
                    $this->logCriticalException($exception, static::LOG_MSG_RECURSIVE_FETCH, ['id' => $productLinkedLocation->getId(), 'sku' => $productLinkedLocation->getSku()], static::LOG_CODE_RECURSIVE_FETCH, ['ou' => $productLinkedLocation->getOrganisationUnitId()]);
                }
            }

            $missingSkuMap = array_diff_ukey(
                $productLink->getStockSkuMap(),
                array_fill_keys($productLinkedLocations->getArrayOf('sku'), true),
                function($productLinkSku, $locationSku) {
                    return strcasecmp($productLinkSku, $locationSku);
                }
            );

            $productQuantifiedLocations = $this->getQuantifiedLocations($productLinkedLocations, $productLink->getStockSkuMap(), $locationsIds);
            foreach ($missingSkuMap as $sku => $qty) {
                $productQuantifiedLocations->attach(
                    $this->buildQuantifiedLocation($location, $sku, $qty)
                );
            }

            $quantifiedLocations->attach(
                new LinkedLocation(
                    $location->getId(),
                    $location->getStockId(),
                    $location->getLocationId(),
                    $location->getOrganisationUnitId(),
                    $location->getSku(),
                    $productQuantifiedLocations
                )
            );
        }

        return $quantifiedLocations;
    }

    /**
     * @return ProductLinks
     */
    protected function getProductLinks(Collection $locations)
    {
        $ouSkuMap = [];

        /** @var Location $location */
        foreach ($locations as $location) {
            $ouSkuMap[] = $location->getOrganisationUnitId() . '-' . $location->getSku();
        }

        return $this->productLinkStorage->fetchCollectionByFilter(
            (new ProductLinkFilter(1, 1))->setOuIdProductSku($ouSkuMap)
        );
    }

    /**
     * @return Filter
     */
    protected function getLinkedLocationsFilter(Collection $locations, ProductLinks $productLinks)
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

        return (new Filter('all', 1))->setLocationId($locations->getArrayOf('locationId'))->setOuIdSku($ouSkuMap);
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

    protected function generateLocationKey(Location $location, $sku = null)
    {
        return implode(
            '-',
            [
                $location->getLocationId(),
                $location->getOrganisationUnitId(),
                strtolower($sku ?: $location->getSku())
            ]
        );
    }

    /**
     * @return Location[]
     */
    protected function keyLinkedLocationsByLocationOuAndSku(Collection $linkedLocations)
    {
        $keydArray = [];

        /** @var Location $linkedLocation */
        foreach ($linkedLocations as $linkedLocation) {
            $key = $this->generateLocationKey($linkedLocation);
            $keydArray[$key] = $linkedLocation;
        }

        return $keydArray;
    }

    /**
     * @return Collection
     */
    protected function buildQuantifiedLinkedLocations(
        Location $location,
        ProductLink $productLink,
        Collection $linkedLocations
    ) {
        $skuQtyMap = $productLink->getStockSkuMap();

        $skuMap = [];
        foreach (array_keys($skuQtyMap) as $sku) {
            $skuMap[strtolower($sku)] = $sku;
        }

        $quantifiedLinkedLocations = new Collection(Location::class, __FUNCTION__, compact('location', 'productLink', 'linkedLocations'));

        /** @var Location $linkedLocation */
        foreach ($linkedLocations as $linkedLocation) {
            $sku = $linkedLocation->getSku();
            if (!isset($skuMap[strtolower($sku)])) {
                continue;
            }

            $quantifiedLinkedLocations->attach(
                $this->buildQuantifiedLocation(
                    $linkedLocation,
                    $linkedLocation->getSku(),
                    $skuQtyMap[$skuMap[strtolower($sku)]]
                )
            );

            unset($skuQtyMap[$skuMap[strtolower($sku)]], $skuMap[strtolower($sku)]);
        }

        // TODO: Refactor
        foreach ($skuQtyMap as $sku => $qty) {
            $quantifiedLinkedLocations->attach(
                $this->buildQuantifiedLocation($location, $sku, $qty)
            );
        }

        return $quantifiedLinkedLocations;
    }

    /**
     * @return QuantifiedLocation
     */
    protected function buildQuantifiedLocation(Location $location, $sku, $qty = 1)
    {
        // TODO: Param for if we get stock or not
        return (new QuantifiedLocation(
            $location->getStockId(),
            $location->getLocationId(),
            $location->getOrganisationUnitId(),
            $sku,
            $location->getOnHand(),
            $location->getAllocated()
        ))->setQuantifier($qty);
    }

    protected function buildQuantifiedLocations(Collection $locations, array $skuQtyMap = [])
    {
        $skuMap = [];
        foreach (array_keys($skuQtyMap) as $sku) {
            $skuMap[strtolower($sku)] = $sku;
        }

        $quantifiedLocations = new Collection(Location::class, $locations->getSourceDescription(), $locations->getSourceFilters());
        $quantifiedLocations->setTotal($locations->getTotal());

        /** @var Location $location */
        foreach ($locations as $location) {
            $sku = $location->getSku();
            $qty = isset($skuMap[strtolower($sku)], $skuQtyMap[$skuMap[strtolower($sku)]]) ? $skuQtyMap[$skuMap[strtolower($sku)]] : 1;
            $quantifiedLocations->attach(
                $this->buildQuantifiedLocation(
                    $location,
                    $sku,
                    $qty
                )
            );
        }

        return $quantifiedLocations;
    }
}
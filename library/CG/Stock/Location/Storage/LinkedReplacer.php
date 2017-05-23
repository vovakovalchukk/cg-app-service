<?php
namespace CG\Stock\Location\Storage;

use CG\Product\Link\Collection as ProductLinks;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Link\Filter as ProductLinkFilter;
use CG\Product\Link\StorageInterface as ProductLinkStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Location\Collection;
use CG\Stock\Location\Entity as Location;
use CG\Stock\Location\Filter;
use CG\Stock\Location\LinkedLocation;
use CG\Stock\Location\QuantifiedLocation;
use CG\Stock\Location\RecursionException;
use CG\Stock\Location\StorageInterface;

class LinkedReplacer implements StorageInterface
{
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

    public function remove($entity)
    {
        $this->locationStorage->remove($entity);
    }

    public function save($entity, array &$ids = [])
    {
        if (isset($ids[$entity->getId()])) {
            throw new RecursionException(sprintf('Already saved stock location with id %s'), $entity->getId());
        }

        $ids[$entity->getId()] = true;
        $location = $this->getQuantifiedLocation($this->locationStorage->save($entity));
        if ($location instanceof LinkedLocation) {
            foreach ($location->getLinkedLocations() as $linkedLocation) {
                $this->save($linkedLocation, $ids);
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

    public function fetchCollectionByFilter(Filter $filter, array &$ids = [])
    {
        $locations = $this->locationStorage->fetchCollectionByFilter($filter);
        foreach ($locations->getIds() as $id) {
            if (isset($ids[$id])) {
                throw new RecursionException(sprintf('Already fetched stock location with id %s'), $id);
            }
            $ids[$id] = true;
        }
        return $this->getQuantifiedLocations($locations, $ids);
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
    protected function getQuantifiedLocations(Collection $locations, array &$ids = [])
    {
        try {
            $productLinks = $this->getProductLinks($locations);
        } catch (NotFound $exception) {
            return $this->buildQuantifiedLocations($locations);
        }

        try {
            $linkedLocations = $this->fetchCollectionByFilter(
                $this->getLinkedLocationsFilter($locations, $productLinks),
                $ids
            );
        } catch (NotFound $exception) {
            $linkedLocations = new Collection(Location::class, __FUNCTION__, ['id' => $locations->getIds()]);
        }

        $productLinksByOuAndSku = $this->keyProductLinksByOuAndSku($productLinks);
        $linkedLocationsByLocationOuAndSku = $this->keyLinkedLocationsByLocationOuAndSku($linkedLocations);

        $quantifiedLocations = new Collection(Location::class, $locations->getSourceFilters(), $locations->getSourceDescription());

        /** @var Location $location */
        foreach ($locations as $location) {
            $key = $location->getOrganisationUnitId() . '-' . strtolower($location->getSku());
            if (!isset($productLinksByOuAndSku[$key])) {
                $quantifiedLocations->attach($this->buildQuantifiedLocation($location, $location->getSku()));
                continue;
            }

            $productLink = $productLinksByOuAndSku[$key];
            $productLinkedLocations = new Collection(Location::class, __FUNCTION__, ['id' => [$location->getId()]]);

            foreach ($productLink->getStockSkuMap() as $sku => $qty) {
                $productLinkedLocationKey = $this->generateLocationKey($location, $sku);
                if (isset($linkedLocationsByLocationOuAndSku[$productLinkedLocationKey])) {
                    $productLinkedLocations->attach($linkedLocationsByLocationOuAndSku[$productLinkedLocationKey]);
                } else {
                    $productLinkedLocations->attach($this->buildQuantifiedLocation($location, $sku, $qty));
                }
            }

            $quantifiedLocations->attach(
                $this->buildQuantifiedLocations($productLinkedLocations, $productLink->getStockSkuMap())
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
            // TODO: Implement `setOuIdSku` on the `ProductLinkFilter`
            (new ProductLinkFilter(1, 1))->setOuIdSku($ouSkuMap)
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
        return (new QuantifiedLocation(
            $location->getStockId(),
            $location->getLocationId(),
            $location->getOrganisationUnitId(),
            $sku,
            0,
            0
        ))->setQuantifier($qty);
    }

    protected function buildQuantifiedLocations(Collection $locations, array $skuQtyMap = [])
    {
        $skuMap = [];
        foreach (array_keys($skuQtyMap) as $sku) {
            $skuMap[strtolower($sku)] = $sku;
        }

        $quantifiedLocations = new Collection(Location::class, $locations->getSourceDescription(), $locations->getSourceFilters());

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
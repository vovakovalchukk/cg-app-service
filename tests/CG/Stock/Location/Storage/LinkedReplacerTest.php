<?php
namespace CG\Test\Stock\Location\Storage;

use CG\FeatureFlags\Lookup\Service as FeatureFlagsService;
use CG\Product\Link\Collection as ProductLinkCollection;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Link\Filter as ProductLinkFilter;
use CG\Product\Link\StorageInterface as ProductLinkStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Collection as StockCollection;
use CG\Stock\Entity as Stock;
use CG\Stock\Filter as StockFilter;
use CG\Stock\Location\Collection as StockLocationCollection;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\Filter as StockLocationFilter;
use CG\Stock\Location\QuantifiedLocation as QuantifiedStockLocation;
use CG\Stock\Location\Storage\LinkedReplacer;
use CG\Stock\Location\StorageInterface as StockLocationStorage;
use CG\Stock\StorageInterface as StockStorage;
use PHPUnit\Framework\TestCase;

class LinkedReplacerTest extends TestCase
{
    /** @var StockLocationStorage $stockLocationStorage */
    protected $stockLocationStorage;
    /** @var StockStorage $stockStorage */
    protected $stockStorage;
    /** @var ProductLinkStorage $productLinkStorage */
    protected $productLinkStorage;
    /** @var bool $enabled */
    protected $enabled;
    /** @var LinkedReplacer $linkReplacer */
    protected $linkReplacer;

    protected function setUp()
    {
        $this->stockLocationStorage = $this->setupStockLocationStorage();
        $this->stockStorage = $this->setupStockStorage();
        $this->productLinkStorage = $this->setupProductLinkStorage();
        $this->enabled = true;

        $this->linkReplacer = new LinkedReplacer(
            $this->stockLocationStorage,
            $this->stockStorage,
            $this->productLinkStorage,
            $this->setupFeatureFlagsService()
        );
    }

    protected function setupStockLocationStorage()
    {
        $stockLocations = [];
        $stockLocationStorage = $this->getMockBuilder(StockLocationStorage::class)->disableOriginalConstructor()->getMock();
        $stockLocationStorage
            ->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(
                function($id) use(&$stockLocations) {
                    $stockLocation = $stockLocations[$id] ?? null;
                    if (!$stockLocation) {
                        throw new NotFound(sprintf('No stock location found matching id %s', $id));
                    }
                    return $stockLocation;
                }
            );
        $stockLocationStorage
            ->expects($this->any())
            ->method('fetchCollectionByFilter')
            ->willReturnCallback(
                function(StockLocationFilter $filter) use(&$stockLocations) {
                    $filteredStockLocations = $stockLocations;
                    if (!empty($stockId = array_fill_keys($filter->getStockId(), true))) {
                        $filteredStockLocations = array_filter(
                            $filteredStockLocations,
                            function(StockLocation $stockLocation) use($stockId) {
                                return isset($stockId[$stockLocation->getStockId()]);
                            }
                        );
                    }
                    if (!empty($locationId = array_fill_keys($filter->getLocationId(), true))) {
                        $filteredStockLocations = array_filter(
                            $filteredStockLocations,
                            function(StockLocation $stockLocation) use($locationId) {
                                return isset($locationId[$stockLocation->getLocationId()]);
                            }
                        );
                    }
                    if (!empty($ouIdSkus = $filter->getOuIdSku())) {
                        $ouIdSkuMap = [];
                        foreach ($ouIdSkus as $ouIdSku) {
                            [$ouId, $sku] = array_pad(explode('-', $ouIdSku, 2), 2, '');
                            $ouIdSkuMap[$ouId][strtolower($sku)] = true;
                        }
                        $filteredStockLocations = array_filter(
                            $filteredStockLocations,
                            function(StockLocation $stockLocation) use($ouIdSkuMap) {
                                try {
                                    /** @var Stock $stock */
                                    $stock = $this->stockStorage->fetch($stockLocation->getStockId());
                                    return isset($ouIdSkuMap[$stock->getOrganisationUnitId()][strtolower($stock->getSku())]);
                                } catch (NotFound $exception) {
                                    return false;
                                }
                            }
                        );
                    }

                    $total = count($filteredStockLocations);
                    if ($filter->getLimit() !== 'all') {
                        $filteredStockLocations = array_splice(
                            $filteredStockLocations,
                            ($filter->getPage() - 1) * $filter->getLimit(),
                            $filter->getLimit()
                        );
                    }

                    if (empty($filteredStockLocations)) {
                        throw new NotFound('No stock locations match filter');
                    }

                    $collection = new StockLocationCollection(StockLocation::class, 'fetchCollectionByFilter', $filter->toArray());
                    $collection->setTotal($total);
                    foreach ($filteredStockLocations as $stockLocation) {
                        $collection->attach($stockLocation);
                    }
                    return $collection;
                }
            );
        $stockLocationStorage
            ->expects($this->any())
            ->method('save')
            ->willReturnCallback(
                function(StockLocation $stockLocation) use(&$stockLocations) {
                    $id = $stockLocation->getId();
                    return $stockLocations[$id] = $stockLocation;
                }
            );
        $stockLocationStorage
            ->expects($this->any())
            ->method('remove')
            ->willReturnCallback(
                function(StockLocation $stockLocation) use(&$stockLocations) {
                    $id = $stockLocation->getId();
                    if (!isset($stockLocations[$id])) {
                        throw new NotFound(sprintf('No stock location found matching id %s', $id));
                    }
                    unset($stockLocations[$id]);
                }
            );
        return $stockLocationStorage;
    }

    protected function setupStockStorage()
    {
        $stocks = [];
        $stockStorage = $this->getMockBuilder(StockStorage::class)->disableOriginalConstructor()->getMock();
        $stockStorage
            ->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(
                function($id) use (&$stocks) {
                    $stock = $stocks[$id] ?? null;
                    if (!$stock) {
                        throw new NotFound(sprintf('No stock found matching id %s', $id));
                    }
                    return $stock;
                }
            );
        $stockStorage
            ->expects($this->any())
            ->method('fetchCollectionByFilter')
            ->willReturnCallback(
                function(StockFilter $filter) use (&$stocks) {
                    $filteredStock = $stocks;
                    if (!empty($id = array_fill_keys($filter->getId(), true))) {
                        $filteredStock = array_filter(
                            $filteredStock,
                            function(Stock $stock) use($id) {
                                return isset($id[$stock->getId()]);
                            }
                        );
                    }
                    if (!empty($organisationUnitId = array_fill_keys($filter->getOrganisationUnitId(), true))) {
                        $filteredStock = array_filter(
                            $filteredStock,
                            function(Stock $stock) use($organisationUnitId) {
                                return isset($organisationUnitId[$stock->getOrganisationUnitId()]);
                            }
                        );
                    }
                    if (!empty($sku = array_fill_keys(array_map('strtolower', $filter->getSku()), true))) {
                        $filteredStock = array_filter(
                            $filteredStock,
                            function(Stock $stock) use($sku) {
                                return isset($sku[strtolower($stock->getSku())]);
                            }
                        );
                    }

                    $total = count($filteredStock);
                    if ($filter->getLimit() !== 'all') {
                        $filteredStock = array_splice(
                            $filteredStock,
                            ($filter->getPage() - 1) * $filter->getLimit(),
                            $filter->getLimit()
                        );
                    }

                    if (empty($filteredStock)) {
                        throw new NotFound('No stock match filter');
                    }

                    $collection = new StockCollection(Stock::class, 'fetchCollectionByFilter', $filter->toArray());
                    $collection->setTotal($total);
                    foreach ($filteredStock as $stock) {
                        $collection->attach($stock);
                    }
                    return $collection;
                }
            );
        $stockStorage
            ->expects($this->any())
            ->method('save')
            ->willReturnCallback(
                function(Stock $stock) use (&$stocks) {
                    static $counter = 1;
                    $id = $stock->getId() ?? $counter++;
                    return $stocks[$id] = $stock->setId($id);
                }
            );
        $stockStorage
            ->expects($this->any())
            ->method('remove')
            ->willReturnCallback(
                function(Stock $stock) use (&$stocks) {
                    $id = $stock->getId();
                    if (!isset($stocks[$id])) {
                        throw new NotFound(sprintf('No stock found matching id %s', $id));
                    }
                    unset($stock[$id]);
                }
            );
        return $stockStorage;
    }

    protected function setupProductLinkStorage()
    {
        $productLinks = [];
        $productLinkStorage = $this->getMockBuilder(ProductLinkStorage::class)->disableOriginalConstructor()->getMock();
        $productLinkStorage
            ->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(
                function($id) use (&$productLinks) {
                    $productLink = $productLinks[$id] ?? null;
                    if (!$productLink) {
                        throw new NotFound(sprintf('No product link found matching id %s', $id));
                    }
                    return $productLink;
                }
            );
        $productLinkStorage
            ->expects($this->any())
            ->method('fetchCollectionByFilter')
            ->willReturnCallback(
                function(ProductLinkFilter $filter) use (&$productLinks) {
                    $filteredProductLinks = $productLinks;
                    if (!empty($organisationUnitId = array_fill_keys($filter->getOrganisationUnitId(), true))) {
                        $filteredProductLinks = array_filter(
                            $filteredProductLinks,
                            function(ProductLink $productLink) use($organisationUnitId) {
                                return isset($organisationUnitId[$productLink->getOrganisationUnitId()]);
                            }
                        );
                    }
                    if (!empty($productSku = array_fill_keys(array_map('strtolower', $filter->getProductSku()), true))) {
                        $filteredProductLinks = array_filter(
                            $filteredProductLinks,
                            function(ProductLink $productLink) use($productSku) {
                                return isset($productSku[strtolower($productLink->getProductSku())]);
                            }
                        );
                    }
                    if (!empty($stockSku = array_fill_keys(array_map('strtolower', $filter->getStockSku()), true))) {
                        $filteredProductLinks = array_filter(
                            $filteredProductLinks,
                            function(ProductLink $productLink) use($stockSku) {
                                foreach ($productLink->getStockSkuMap() as $sku => $qty) {
                                    if (isset($stockSku[strtolower($sku)])) {
                                        return true;
                                    }
                                }
                                return false;
                            }
                        );
                    }
                    if (!empty($ouIdProductSku = array_fill_keys($filter->getOuIdProductSku(), true))) {
                        $filteredProductLinks = array_filter(
                            $filteredProductLinks,
                            function(ProductLink $productLink) use($ouIdProductSku) {
                                return isset($ouIdProductSku[$productLink->getId()]);
                            }
                        );
                    }

                    $total = count($filteredProductLinks);
                    if ($filter->getLimit() !== 'all') {
                        $filteredProductLinks = array_splice(
                            $filteredProductLinks,
                            ($filter->getPage() - 1) * $filter->getLimit(),
                            $filter->getLimit()
                        );
                    }

                    if (empty($filteredProductLinks)) {
                        throw new NotFound('No product link match filter');
                    }

                    $collection = new ProductLinkCollection(ProductLink::class, 'fetchCollectionByFilter', $filter->toArray());
                    $collection->setTotal($total);
                    foreach ($filteredProductLinks as $productLink) {
                        $collection->attach($productLink);
                    }
                    return $collection;
                }
            );
        $productLinkStorage
            ->expects($this->any())
            ->method('save')
            ->willReturnCallback(
                function(ProductLink $productLink) use (&$productLinks) {
                    $id = $productLink->getId();
                    return $productLinks[$id] = $productLink;
                }
            );
        $productLinkStorage
            ->expects($this->any())
            ->method('remove')
            ->willReturnCallback(
                function(ProductLink $productLink) use (&$productLinks) {
                    $id = $productLink->getId();
                    if (!isset($productLinks[$id])) {
                        throw new NotFound(sprintf('No product link found matching id %s', $id));
                    }
                    unset($productLinks[$id]);
                }
            );
        return $productLinkStorage;
    }

    protected function setupFeatureFlagsService()
    {
        $featureFlags = $this->getMockBuilder(FeatureFlagsService::class)->disableOriginalConstructor()->getMock();
        $featureFlags->expects($this->any())->method('featureEnabledForEntity')->willReturn($this->enabled);
        $featureFlags->expects($this->any())->method('featureEnabledForOu')->willReturn($this->enabled);
        return $featureFlags;
    }

    public function testReturnsQuantifiedEntity()
    {
        $this->stockLocationStorage->save(
            $stockLocation = new StockLocation(1, 1, 10, 5)
        );

        /** @var StockLocation $quantifiedStockLocation */
        $quantifiedStockLocation = $this->linkReplacer->fetch($stockLocation->getId());

        $this->assertInstanceOf(
            QuantifiedStockLocation::class,
            $quantifiedStockLocation,
            'Stock location was not replaced with a quantified version'
        );
        $this->assertEquals(
            $stockLocation->toArray(),
            $quantifiedStockLocation->toArray(),
            'Quantified stock location does not contain the same information as the underlying stock location'
        );
    }
}

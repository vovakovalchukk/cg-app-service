<?php
namespace CG\Test\Stock\Location\Storage;

use CG\FeatureFlags\Lookup\Service as FeatureFlagsService;
use CG\Product\LinkLeaf\Collection as ProductLinkLeafCollection;
use CG\Product\LinkLeaf\Entity as ProductLinkLeaf;
use CG\Product\LinkLeaf\Filter as ProductLinkLeafFilter;
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
use CG\TestAsset\Product\LinkLeaf\StorageInterface as ProductLinkLeafStorage;
use PHPUnit\Framework\TestCase;

class LinkedReplacerTest extends TestCase
{
    /** @var StockLocationStorage $stockLocationStorage */
    protected $stockLocationStorage;
    /** @var StockStorage $stockStorage */
    protected $stockStorage;
    /** @var ProductLinkLeafStorage $productLinkLeafStorage */
    protected $productLinkLeafStorage;
    /** @var bool $enabled */
    protected $enabled;
    /** @var LinkedReplacer $linkReplacer */
    protected $linkReplacer;

    protected function setUp()
    {
        $this->stockLocationStorage = $this->setupStockLocationStorage();
        $this->stockStorage = $this->setupStockStorage();
        $this->productLinkLeafStorage = $this->setupProductLinkLeafStorage();
        $this->enabled = true;

        $this->linkReplacer = new LinkedReplacer(
            $this->stockLocationStorage,
            $this->stockStorage,
            $this->productLinkLeafStorage,
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

    protected function setupProductLinkLeafStorage()
    {
        $productLinkLeafs = [];
        $productLinkLeafStorage = $this->getMockBuilder(ProductLinkLeafStorage::class)->disableOriginalConstructor()->getMock();
        $productLinkLeafStorage
            ->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(
                function($id) use (&$productLinkLeafs) {
                    $productLinkLeaf = $productLinkLeafs[$id] ?? null;
                    if (!$productLinkLeaf) {
                        throw new NotFound(sprintf('No product link leaf found matching id %s', $id));
                    }
                    return $productLinkLeaf;
                }
            );
        $productLinkLeafStorage
            ->expects($this->any())
            ->method('fetchCollectionByFilter')
            ->willReturnCallback(
                function(ProductLinkLeafFilter $filter) use (&$productLinkLeafs) {
                    $filteredProductLinkLeafs = $productLinkLeafs;
                    if (!empty($ouIdProductSku = array_fill_keys($filter->getOuIdProductSku(), true))) {
                        $filteredProductLinkLeafs = array_filter(
                            $filteredProductLinkLeafs,
                            function(ProductLinkLeaf $productLinkLeaf) use($ouIdProductSku) {
                                return isset($ouIdProductSku[$productLinkLeaf->getId()]);
                            }
                        );
                    }

                    $total = count($filteredProductLinkLeafs);
                    if ($filter->getLimit() !== 'all') {
                        $filteredProductLinkLeafs = array_splice(
                            $filteredProductLinkLeafs,
                            ($filter->getPage() - 1) * $filter->getLimit(),
                            $filter->getLimit()
                        );
                    }

                    if (empty($filteredProductLinkLeafs)) {
                        throw new NotFound('No product link leaf match filter');
                    }

                    $collection = new ProductLinkLeafCollection(ProductLinkLeaf::class, 'fetchCollectionByFilter', $filter->toArray());
                    $collection->setTotal($total);
                    foreach ($filteredProductLinkLeafs as $productLinkLeaf) {
                        $collection->attach($productLinkLeaf);
                    }
                    return $collection;
                }
            );
        $productLinkLeafStorage
            ->expects($this->any())
            ->method('save')
            ->willReturnCallback(
                function(ProductLinkLeaf $productLinkLeaf) use (&$productLinkLeafs) {
                    $id = $productLinkLeaf->getId();
                    return $productLinkLeafs[$id] = $productLinkLeaf;
                }
            );
        $productLinkLeafStorage
            ->expects($this->any())
            ->method('remove')
            ->willReturnCallback(
                function(ProductLinkLeaf $productLinkLeaf) use (&$productLinkLeafs) {
                    $id = $productLinkLeaf->getId();
                    if (!isset($productLinkLeafs[$id])) {
                        throw new NotFound(sprintf('No product link leaf found matching id %s', $id));
                    }
                    unset($productLinkLeafs[$id]);
                }
            );
        return $productLinkLeafStorage;
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
        $stockLocation = $this->createStockLocation('sku', 10, 5);

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

    public function testLoadsAllLinkedLocations()
    {
        $this->createProductLinkLeaf('link', ['sku1' => 1, 'sku2' => 1, 'sku3' => 1, 'sku4' => 1]);
        $skuStockData = [
            'sku1' => ['onHand' => 22, 'allocated' => 3],
            'sku2' => ['onHand' => 13, 'allocated' => 0],
            'sku3' => ['onHand' => 41, 'allocated' => 2],
            'sku4' => ['onHand' => 12, 'allocated' => 4],
        ];

        $stockLocation = $this->createStockLocation('link');
        foreach ($skuStockData as $sku => $stockData) {
            $this->createStockLocation($sku, $stockData['onHand'], $stockData['allocated']);
        }

        /** @var StockLocation $quantifiedStockLocation */
        $quantifiedStockLocation = $this->linkReplacer->fetch($stockLocation->getId());
        $this->assertEquals(
            12,
            $quantifiedStockLocation->getOnHand(),
            'Linked location did not return expected on hand stock'
        );
        $this->assertEquals(
            4,
            $quantifiedStockLocation->getAllocated(),
            'Linked location did not return expected allocated stock'
        );
        $this->assertEquals(
            8,
            $quantifiedStockLocation->getAvailable(),
            'Linked location did not return expected available stock'
        );
    }

    public function testLoadsQuantifiedLinkedLocations()
    {
        $this->createProductLinkLeaf('link', ['sku1' => 2, 'sku2' => 1, 'sku3' => 1, 'sku4' => 1]);
        $skuStockData = [
            'sku1' => ['onHand' => 22, 'allocated' => 3],
            'sku2' => ['onHand' => 13, 'allocated' => 0],
            'sku3' => ['onHand' => 41, 'allocated' => 2],
            'sku4' => ['onHand' => 12, 'allocated' => 4],
        ];

        $stockLocation = $this->createStockLocation('link');
        foreach ($skuStockData as $sku => $stockData) {
            $this->createStockLocation($sku, $stockData['onHand'], $stockData['allocated']);
        }

        /** @var StockLocation $quantifiedStockLocation */
        $quantifiedStockLocation = $this->linkReplacer->fetch($stockLocation->getId());
        $this->assertEquals(
            11,
            $quantifiedStockLocation->getOnHand(),
            'Linked location did not return expected on hand stock'
        );
        $this->assertEquals(
            4,
            $quantifiedStockLocation->getAllocated(),
            'Linked location did not return expected allocated stock'
        );
        $this->assertEquals(
            7,
            $quantifiedStockLocation->getAvailable(),
            'Linked location did not return expected available stock'
        );
    }

    public function testAccountsForMissingLocations()
    {
        $this->createProductLinkLeaf('link', ['sku1' => 1, 'sku2' => 1, 'sku3' => 1, 'sku4' => 1, 'sku5' => 1]);
        $skuStockData = [
            'sku1' => ['onHand' => 22, 'allocated' => 3],
            'sku2' => ['onHand' => 13, 'allocated' => 0],
            'sku3' => ['onHand' => 41, 'allocated' => 2],
            'sku4' => ['onHand' => 12, 'allocated' => 4],
        ];

        $stockLocation = $this->createStockLocation('link');
        foreach ($skuStockData as $sku => $stockData) {
            $this->createStockLocation($sku, $stockData['onHand'], $stockData['allocated']);
        }

        /** @var StockLocation $quantifiedStockLocation */
        $quantifiedStockLocation = $this->linkReplacer->fetch($stockLocation->getId());
        $this->assertEquals(
            0,
            $quantifiedStockLocation->getOnHand(),
            'Linked location did not return expected on hand stock'
        );
        $this->assertEquals(
            4,
            $quantifiedStockLocation->getAllocated(),
            'Linked location did not return expected allocated stock'
        );
        $this->assertEquals(
            -4,
            $quantifiedStockLocation->getAvailable(),
            'Linked location did not return expected available stock'
        );
    }

    protected function createStockLocation($sku, $onHand = 0, $allocated = 0): StockLocation
    {
        $this->stockStorage->save(
            $stock = new Stock(1, $sku)
        );
        return $this->stockLocationStorage->save(
            new StockLocation($stock->getId(), 1, $onHand, $allocated)
        );
    }

    protected function createProductLinkLeaf($productSku, array $stockSkuMap): ProductLinkLeaf
    {
        return $this->productLinkLeafStorage->save(
            new ProductLinkLeaf(1, $productSku, $stockSkuMap)
        );
    }
}

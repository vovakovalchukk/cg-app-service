<?php
namespace CG\Test\Stock\Location\Storage;

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
use CG\Stock\Location\LinkedLocation as LinkedStockLocation;
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
    /** @var LinkedReplacer $linkReplacer */
    protected $linkReplacer;

    protected function setUp()
    {
        $this->stockLocationStorage = $this->setupStockLocationStorage();
        $this->stockStorage = $this->setupStockStorage();
        $this->productLinkLeafStorage = $this->setupProductLinkLeafStorage();

        $this->linkReplacer = new LinkedReplacer(
            $this->stockLocationStorage,
            $this->stockStorage,
            $this->productLinkLeafStorage
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
                    return $stockLocations[$id] = clone $stockLocation;
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
                    return $stocks[$id] = clone $stock->setId($id);
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
                    return $productLinkLeafs[$id] = clone $productLinkLeaf;
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

    public function testFetchQuantifiedLocation()
    {
        $stockLocation = $this->createStockLocation('sku', 10, 5);
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

    public function getTestStockData()
    {
        $skuStockData = [
            'sku1' => ['onHand' => 22, 'allocated' => 3],
            'sku2' => ['onHand' => 13, 'allocated' => 0],
            'sku3' => ['onHand' => 41, 'allocated' => 2],
            'sku4' => ['onHand' => 12, 'allocated' => 4],
        ];

        yield 'simple' => [
            'link',
            ['sku1' => 1, 'sku2' => 1, 'sku3' => 1, 'sku4' => 1],
            [],
            ['onHand' => 12, 'allocated' => 4],
            $skuStockData
        ];
        yield 'quantified' => [
            'link',
            ['sku1' => 2, 'sku2' => 1, 'sku3' => 1, 'sku4' => 1],
            [],
            ['onHand' => 11, 'allocated' => 4],
            $skuStockData
        ];
        yield 'missing' => [
            'link',
            ['sku1' => 1, 'sku2' => 1, 'sku3' => 1, 'sku4' => 1, 'sku5' => 1],
            ['sku5'],
            ['onHand' => 0, 'allocated' => 4],
            $skuStockData
        ];
    }

    /**
     * @dataProvider getTestStockData
     */
    public function testFetchLinkedLocation(
        string $linkSku,
        array $linkMap,
        array $missingSkus,
        array $linkStock,
        array $skuStockData
    ) {
        $this->createProductLinkLeaf($linkSku, $linkMap);

        $stockLocation = $this->createStockLocation('link');
        foreach ($skuStockData as $sku => $stockData) {
            $this->createStockLocation($sku, $stockData['onHand'], $stockData['allocated']);
        }

        /** @var LinkedStockLocation $linkedStockLocation */
        $linkedStockLocation = $this->linkReplacer->fetch($stockLocation->getId());
        $this->assertInstanceOf(
            LinkedStockLocation::class,
            $linkedStockLocation,
            'Stock location was not replaced with a quantified version'
        );
        $this->assertEquals(
            $missingSkus,
            $linkedStockLocation->getLinkedLocations()->getMissingSkus(),
            'Linked location did not account for expected missing skus'
        );
        $this->assertEquals(
            $linkStock['onHand'],
            $linkedStockLocation->getOnHand(),
            'Linked location did not return expected on hand stock'
        );
        $this->assertEquals(
            $linkStock['allocated'],
            $linkedStockLocation->getAllocated(),
            'Linked location did not return expected allocated stock'
        );
        $this->assertEquals(
            $linkStock['onHand'] - $linkStock['allocated'],
            $linkedStockLocation->getAvailable(),
            'Linked location did not return expected available stock'
        );
    }

    public function testSaveQuantifiedLocation()
    {
        $stockLocation = $this->createStockLocation('sku', 10, 5);
        $quantifiedStockLocation = $this->linkReplacer->save(
            $stockLocation->setOnHand(9)->setAllocated(4)
        );

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

    public function getTestStockUpdateData()
    {
        yield 'simpleOnHand' => [
            'link',
            ['sku1' => 1, 'sku2' => 1, 'sku3' => 1, 'sku4' => 1],
            ['onHand' => 12, 'savedOnHand' => 15, 'allocated' => 4, 'savedAllocated' => 4],
            [
                'sku1' => ['onHand' => 22, 'expectedOnHand' => 25, 'allocated' => 3, 'expectedAllocated' => 3],
                'sku2' => ['onHand' => 13, 'expectedOnHand' => 16, 'allocated' => 0, 'expectedAllocated' => 0],
                'sku3' => ['onHand' => 41, 'expectedOnHand' => 44, 'allocated' => 2, 'expectedAllocated' => 2],
                'sku4' => ['onHand' => 12, 'expectedOnHand' => 15, 'allocated' => 4, 'expectedAllocated' => 4],
            ]
        ];

        yield 'quantifiedOnHand' => [
            'link',
            ['sku1' => 2, 'sku2' => 1, 'sku3' => 1, 'sku4' => 1],
            ['onHand' => 11, 'savedOnHand' => 15, 'allocated' => 4, 'savedAllocated' => 4],
            [
                'sku1' => ['onHand' => 23, 'expectedOnHand' => 31, 'allocated' => 3, 'expectedAllocated' => 3],
                'sku2' => ['onHand' => 13, 'expectedOnHand' => 17, 'allocated' => 0, 'expectedAllocated' => 0],
                'sku3' => ['onHand' => 41, 'expectedOnHand' => 45, 'allocated' => 2, 'expectedAllocated' => 2],
                'sku4' => ['onHand' => 12, 'expectedOnHand' => 16, 'allocated' => 4, 'expectedAllocated' => 4],
            ]
        ];

        yield 'simpleAllocated' => [
            'link',
            ['sku1' => 1, 'sku2' => 1, 'sku3' => 1, 'sku4' => 1],
            ['onHand' => 12, 'savedOnHand' => 12, 'allocated' => 4, 'savedAllocated' => 5],
            [
                'sku1' => ['onHand' => 22, 'expectedOnHand' => 22, 'allocated' => 3, 'expectedAllocated' => 4],
                'sku2' => ['onHand' => 13, 'expectedOnHand' => 13, 'allocated' => 0, 'expectedAllocated' => 1],
                'sku3' => ['onHand' => 41, 'expectedOnHand' => 41, 'allocated' => 2, 'expectedAllocated' => 3],
                'sku4' => ['onHand' => 12, 'expectedOnHand' => 12, 'allocated' => 4, 'expectedAllocated' => 5],
            ]
        ];

        yield 'quantifiedAllocated' => [
            'link',
            ['sku1' => 2, 'sku2' => 1, 'sku3' => 1, 'sku4' => 1],
            ['onHand' => 11, 'savedOnHand' => 11, 'allocated' => 4, 'savedAllocated' => 5],
            [
                'sku1' => ['onHand' => 23, 'expectedOnHand' => 23, 'allocated' => 3, 'expectedAllocated' => 5],
                'sku2' => ['onHand' => 13, 'expectedOnHand' => 13, 'allocated' => 0, 'expectedAllocated' => 1],
                'sku3' => ['onHand' => 41, 'expectedOnHand' => 41, 'allocated' => 2, 'expectedAllocated' => 3],
                'sku4' => ['onHand' => 12, 'expectedOnHand' => 12, 'allocated' => 4, 'expectedAllocated' => 5],
            ]
        ];
    }

    /**
     * @dataProvider getTestStockUpdateData
     */
    public function testSaveLinkedLocation(
        string $linkSku,
        array $linkMap,
        array $linkStock,
        array $skuStockData
    ) {
        $this->createProductLinkLeaf($linkSku, $linkMap);
        $stockLocation = $this->createStockLocation($linkSku);

        $skuIdMap = [];
        foreach ($skuStockData as $sku => $stockData) {
            $skuIdMap[$sku] = $this->createStockLocation($sku, $stockData['onHand'], $stockData['allocated'])->getId();
        }

        $quantifiedStockLocation = $this->linkReplacer->fetch($stockLocation->getId());
        $this->assertEquals(
            $linkStock['onHand'],
            $quantifiedStockLocation->getOnHand(),
            'Parent location did not have it\'s onHand stock updated'
        );
        $this->assertEquals(
            $linkStock['allocated'],
            $quantifiedStockLocation->getAllocated(),
            'Parent location did not have it\'s allocated stock updated'
        );
        $this->assertEquals(
            $linkStock['onHand'] - $linkStock['allocated'],
            $quantifiedStockLocation->getAvailable(),
            'Parent location did not have it\'s available stock updated'
        );

        $savedStockLocation = $this->linkReplacer->save(
            $quantifiedStockLocation->setOnHand($linkStock['savedOnHand'])->setAllocated($linkStock['savedAllocated'])
        );

        $this->assertEquals(
            $linkStock['savedOnHand'],
            $savedStockLocation->getOnHand(),
            'Saved parent location did not have it\'s onHand stock updated'
        );
        $this->assertEquals(
            $linkStock['savedAllocated'],
            $savedStockLocation->getAllocated(),
            'Saved parent location did not have it\'s allocated stock updated'
        );
        $this->assertEquals(
            $linkStock['savedOnHand'] - $linkStock['savedAllocated'],
            $savedStockLocation->getAvailable(),
            'Saved parent location did not have it\'s available stock updated'
        );

        foreach ($skuIdMap as $sku => $stockLocationId) {
            $this->assertEquals(
                $skuStockData[$sku]['expectedOnHand'],
                $this->linkReplacer->fetch($stockLocationId)->getOnHand(),
                sprintf('Linked location (%s) did not have it\'s onHand stock updated', $sku)
            );
            $this->assertEquals(
                $skuStockData[$sku]['expectedAllocated'],
                $this->linkReplacer->fetch($stockLocationId)->getAllocated(),
                sprintf('Linked location (%s) did not have it\'s allocated stock updated', $sku)
            );
            $this->assertEquals(
                $skuStockData[$sku]['expectedOnHand'] - $skuStockData[$sku]['expectedAllocated'],
                $this->linkReplacer->fetch($stockLocationId)->getAvailable(),
                sprintf('Linked location (%s) did not have it\'s available stock updated', $sku)
            );
        }
    }

    /**
     * @expectedException \CG\Stdlib\Exception\Runtime\ValidationMessagesException
     */
    public function testPreventSaveIfMissingLocations()
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

        /** @var LinkedStockLocation $linkedStockLocation */
        $linkedStockLocation = $this->linkReplacer->fetch($stockLocation->getId());
        $this->assertInstanceOf(
            LinkedStockLocation::class,
            $linkedStockLocation,
            'Stock location was not replaced with a quantified version'
        );
        $this->assertEquals(
            ['sku5'],
            $linkedStockLocation->getLinkedLocations()->getMissingSkus(),
            'Linked location does not know about missing location'
        );
        $this->linkReplacer->save($linkedStockLocation->setOnHand(15));
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

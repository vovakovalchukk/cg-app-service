<?php
namespace CG\Test\PurchaseOrder;

use CG\PurchaseOrder\Entity as PurchaseOrder;
use CG\PurchaseOrder\Item\Collection as ItemCollection;
use CG\PurchaseOrder\Item\Entity as Item;
use CG\PurchaseOrder\Item\Mapper as PurchaseOrderItemMapper;
use CG\PurchaseOrder\Item\RestService as PurchaseOrderItemService;
use CG\PurchaseOrder\Mapper;
use CG\PurchaseOrder\RestService;
use CG\PurchaseOrder\Status as PurchaseOrderStatus;
use CG\PurchaseOrder\StorageInterface;
use CG\Stock\Gearman\Generator\AdjustOnPurchaseOrder as AdjustStockOnPurchaseOrderGenerator;
use CG\Stock\Gearman\Generator\StockImport as StockImportGenerator;
use CG\Test\PurchaseOrder\Item\GetMockPurchaseOrderItemTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Zend\EventManager\GlobalEventManager as EventManager;

class RestServiceTest extends TestCase
{
    use GetMockPurchaseOrderTrait;
    use GetMockPurchaseOrderItemTrait;

    /** @var RestService */
    protected $restService;
    /** @var MockObject */
    protected $repository;
    /** @var MockObject */
    protected $adjustStockOnPurchaseOrderGenerator;

    /** @var array */
    protected $adjustStockOnPurchaseOrderJobs = [];

    public function setUp()
    {
        $this->repository = $this->getMockBuilder(StorageInterface::class)->getMock();
        $mapper = $this->getMockBuilder(Mapper::class)->disableOriginalConstructor()->getMock();
        $poItemService = $this->getMockBuilder(PurchaseOrderItemService::class)->disableOriginalConstructor()->getMock();
        $poItemMapper = $this->getMockBuilder(PurchaseOrderItemMapper::class)->disableOriginalConstructor()->getMock();
        $stockImportGenerator = $this->getMockBuilder(StockImportGenerator::class)->disableOriginalConstructor()->getMock();
        $eventManager = $this->getMockBuilder(EventManager::class)->disableOriginalConstructor()->getMock();
        $this->adjustStockOnPurchaseOrderGenerator = $this->getMockBuilder(AdjustStockOnPurchaseOrderGenerator::class)->disableOriginalConstructor()->getMock();

        $this->adjustStockOnPurchaseOrderJobs = [];
        $this->adjustStockOnPurchaseOrderGenerator->expects($this->any())
            ->method('__invoke')
            ->will($this->returnCallback(function($item, $adjustment)
            {
                $this->adjustStockOnPurchaseOrderJobs[$item->getId()] = $adjustment;
            }));

        $this->restService = new RestService(
            $this->repository, $mapper, $poItemService, $poItemMapper, $stockImportGenerator, $eventManager, $this->adjustStockOnPurchaseOrderGenerator
        );
    }

    public function testCompletingPOTriggersUpdateOfStockOnPurchaseOrderCounts()
    {
        $purchaseOrder = $this->givenAPurchaseOrderIsNotYetCompleted();
        $updatedPurchaseOrder = $this->whenThePurchaseOrderIsCompleted($purchaseOrder);
        $this->thenStockOnPurchaseOrderCountsWillBeReducedByTheAmountsOnPO($updatedPurchaseOrder);
    }

    public function testReSavingACompletedPODoesntTriggersUpdateOfStockOnPurchaseOrderCounts()
    {
        $purchaseOrder = $this->givenAPurchaseOrderIAlreadyCompleted();
        $updatedPurchaseOrder = $this->whenThePurchaseOrderIsCompleted($purchaseOrder);
        $this->thenStockOnPurchaseOrderCountsShouldNotBeReduced($updatedPurchaseOrder);
    }

    protected function givenAPurchaseOrderIsNotYetCompleted(): MockObject
    {
        return $this->getMockPurchaseOrder(1, PurchaseOrderStatus::IN_PROGRESS);
    }

    protected function givenAPurchaseOrderIAlreadyCompleted(): MockObject
    {
        return $this->getMockPurchaseOrder(1, PurchaseOrderStatus::COMPLETE);
    }

    protected function whenThePurchaseOrderIsCompleted(MockObject $purchaseOrder): MockObject
    {
        $this->repository->expects($this->any())->method('fetch')->willReturn($purchaseOrder);

        $items = [[1, 'A', 1], [2, 'B', 2]];
        $updatedPurchaseOrder = $this->getMockPurchaseOrder($purchaseOrder->getId(), PurchaseOrderStatus::COMPLETE, $items);

        $this->restService->save($updatedPurchaseOrder);
        return $updatedPurchaseOrder;
    }

    protected function thenStockOnPurchaseOrderCountsWillBeReducedByTheAmountsOnPO(MockObject $updatedPurchaseOrder): void
    {
        foreach ($updatedPurchaseOrder->getItems() as $item) {
            $this->assertArrayHasKey($item->getId(), $this->adjustStockOnPurchaseOrderJobs);
            $this->assertEquals(0 - $item->getQuantity(), $this->adjustStockOnPurchaseOrderJobs[$item->getId()]);
        }
    }

    protected function thenStockOnPurchaseOrderCountsShouldNotBeReduced(): void
    {
        $this->assertEmpty($this->adjustStockOnPurchaseOrderJobs);
    }
}
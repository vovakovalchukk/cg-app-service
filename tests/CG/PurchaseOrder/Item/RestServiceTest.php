<?php
namespace CG\Test\PurchaseOrder\Item;

use CG\PurchaseOrder\Item\Mapper;
use CG\PurchaseOrder\Item\Nginx\Cache\Invalidator as NginxCacheInvalidator;
use CG\PurchaseOrder\Item\RestService;
use CG\PurchaseOrder\Item\StorageInterface;
use CG\PurchaseOrder\Status as PurchaseOrderStatus;
use CG\PurchaseOrder\StorageInterface as PurchaseOrderStorage;
use CG\Stock\Gearman\Generator\AdjustOnPurchaseOrder as AdjustStockOnPurchaseOrderGenerator;
use CG\Test\PurchaseOrder\GetMockPurchaseOrderTrait;
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
    /** @var MockObject */
    protected $purchaseOrderStorage;

    public function setUp()
    {
        $eventManager = $this->getMockBuilder(EventManager::class)->disableOriginalConstructor()->getMock();
        $this->repository = $this->getMockBuilder(StorageInterface::class)->getMock();
        $mapper = $this->getMockBuilder(Mapper::class)->disableOriginalConstructor()->getMock();
        $nginxCacheInvalidator = $this->getMockBuilder(NginxCacheInvalidator::class)->disableOriginalConstructor()->getMock();
        $this->adjustStockOnPurchaseOrderGenerator = $this->getMockBuilder(AdjustStockOnPurchaseOrderGenerator::class)->disableOriginalConstructor()->getMock();
        $this->purchaseOrderStorage = $this->getMockBuilder(PurchaseOrderStorage::class)->getMock();

        $this->repository->expects($this->any())->method('save')->will($this->returnArgument(0));

        $this->restService = new RestService(
            $eventManager, $this->repository, $mapper, $nginxCacheInvalidator, $this->adjustStockOnPurchaseOrderGenerator, $this->purchaseOrderStorage
        );
    }

    public function testCreatingItemAddsQuantityToStockOnPurchaseOrder()
    {
        $item = $this->givenANewPurchaseOrderItem();
        // Have to do the 'Then' before the 'When' here so we can set up the watch in advance of the action
        $this->thenStockOnPurchaseOrderCountsWillBeIncreasedByTheAmountOnTheItem($item);
        $this->whenThePurchaseOrderItemIsSaved($item);
    }

    public function testIncreasingItemQuantityAdjustsStockOnPurchaseOrderByTheDifference()
    {
        $difference = 2;
        $item = $this->givenAnExistingPurchaseOrderItem();
        // Have to do the 'Then' before the 'When'
        $this->thenStockOnPurchaseOrderCountsWillBeAdjustedByTheDifference($item, $difference);
        $this->whenThePurchaseOrderItemQuantityIsUpdated($item, $difference);
    }

    public function testDecreasingItemQuantityAdjustsStockOnPurchaseOrderByTheDifference()
    {
        $difference = -2;
        $item = $this->givenAnExistingPurchaseOrderItem();
        // Have to do the 'Then' before the 'When'
        $this->thenStockOnPurchaseOrderCountsWillBeAdjustedByTheDifference($item, $difference);
        $this->whenThePurchaseOrderItemQuantityIsUpdated($item, $difference);
    }

    public function testDeletingItemFromIncompletePORemovesQuantityFromStockOnPurchaseOrder()
    {
        $item = $this->givenAnExistingPurchaseOrderItem();
        $this->givenAnIncompletePurchaseOrder();
        // Have to do the 'Then' before the 'When'
        $this->thenStockOnPurchaseOrderCountsWillBeDecreasedByTheAmountOnTheItem($item);
        $this->whenThePurchaseOrderItemIsDeleted($item);
    }

    public function testDeletingItemFromCompletePODoesntAdjustStockOnPurchaseOrder()
    {
        $item = $this->givenAnExistingPurchaseOrderItem();
        $this->givenACompletePurchaseOrder();
        // Have to do the 'Then' before the 'When'
        $this->thenStockOnPurchaseOrderCountsWillNotBeAdjusted($item);
        $this->whenThePurchaseOrderItemIsDeleted($item);
    }

    protected function givenANewPurchaseOrderItem(): MockObject
    {
        return $this->getMockPurchaseOrderItem(null, 'A', 2);
    }

    protected function givenAnExistingPurchaseOrderItem(): MockObject
    {
        $id = 1;
        $item = $this->getMockPurchaseOrderItem($id, 'A', 2);
        $this->repository->expects($this->any())->method('fetch')->with($id)->willReturn($item);
        return $item;
    }

    protected function givenAnIncompletePurchaseOrder(): MockObject
    {
        $purchaseOrder = $this->getMockPurchaseOrder(1, PurchaseOrderStatus::IN_PROGRESS);
        $this->purchaseOrderStorage->expects($this->any())->method('fetch')->willReturn($purchaseOrder);
        return $purchaseOrder;
    }

    protected function givenACompletePurchaseOrder(): MockObject
    {
        $purchaseOrder = $this->getMockPurchaseOrder(1, PurchaseOrderStatus::COMPLETE);
        $this->purchaseOrderStorage->expects($this->any())->method('fetch')->willReturn($purchaseOrder);
        return $purchaseOrder;
    }

    protected function whenThePurchaseOrderItemIsSaved(MockObject $item): void
    {
        $this->restService->save($item);
    }

    protected function whenThePurchaseOrderItemQuantityIsUpdated(MockObject $item, int $difference): void
    {
        $updatedItem = $this->getMockPurchaseOrderItem($item->getId(), $item->getSku(), $item->getQuantity() + $difference);
        $this->restService->save($updatedItem);
    }

    protected function whenThePurchaseOrderItemIsDeleted(MockObject $item)
    {
        $this->restService->remove($item);
    }

    protected function thenStockOnPurchaseOrderCountsWillBeIncreasedByTheAmountOnTheItem(MockObject $item): void
    {
        $this->thenStockOnPurchaseOrderCountsWillBeAdjustedByTheDifference($item, $item->getQuantity());
    }

    protected function thenStockOnPurchaseOrderCountsWillBeAdjustedByTheDifference(MockObject $item, int $difference): void
    {
        $this->adjustStockOnPurchaseOrderGenerator->expects($this->once())
            ->method('__invoke')
            ->with($item, $difference);
    }

    protected function thenStockOnPurchaseOrderCountsWillBeDecreasedByTheAmountOnTheItem(MockObject $item): void
    {
        $this->thenStockOnPurchaseOrderCountsWillBeAdjustedByTheDifference($item, 0 - $item->getQuantity());
    }

    protected function thenStockOnPurchaseOrderCountsWillNotBeAdjusted(): void
    {
        $this->adjustStockOnPurchaseOrderGenerator->expects($this->never())->method('__invoke');
    }
}
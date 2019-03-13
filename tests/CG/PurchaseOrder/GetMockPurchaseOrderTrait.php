<?php
namespace CG\Test\PurchaseOrder;

use CG\PurchaseOrder\Entity as PurchaseOrder;
use CG\PurchaseOrder\Item\Collection as ItemCollection;
use CG\PurchaseOrder\Item\Entity as Item;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

trait GetMockPurchaseOrderTrait
{
    protected function getMockPurchaseOrder(int $id, string $status, array $itemsConfig = []): MockObject
    {
        $purchaseOrder = $this->getMockBuilder(PurchaseOrder::class)->disableOriginalConstructor()->getMock();
        $purchaseOrder->expects($this->any())->method('getId')->willReturn($id);
        $purchaseOrder->expects($this->any())->method('getStatus')->willReturn($status);

        $items = new ItemCollection(Item::class, 'TEST');
        foreach ($itemsConfig as $itemConfig) {
            $item = $this->getMockPurchaseOrderItem(...$itemConfig);
            $items->attach($item);
        }
        $purchaseOrder->expects($this->any())->method('getItems')->willReturn($items);

        return $purchaseOrder;
    }

    abstract public function getMockBuilder($className);
}
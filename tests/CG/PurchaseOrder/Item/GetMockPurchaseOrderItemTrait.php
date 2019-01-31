<?php
namespace CG\Test\PurchaseOrder\Item;

use CG\PurchaseOrder\Item\Entity as Item;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

trait GetMockPurchaseOrderItemTrait
{
    protected function getMockPurchaseOrderItem(?int $id, string $sku, int $quantity): MockObject
    {
        $item = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->getMock();
        $item->expects($this->any())->method('getId')->willReturn($id);
        $item->expects($this->any())->method('getSku')->willReturn($sku);
        $item->expects($this->any())->method('getQuantity')->willReturn($quantity);
        return $item;
    }

    abstract public function getMockBuilder($className);
}
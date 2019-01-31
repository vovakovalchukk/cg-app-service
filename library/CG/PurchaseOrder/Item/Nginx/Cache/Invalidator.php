<?php
namespace CG\PurchaseOrder\Item\Nginx\Cache;

use CG\CGLib\Nginx\Cache\AbstractInvalidator;
use CG\CGLib\Nginx\Cache\Resource;
use CG\PurchaseOrder\Item\Entity as Item;

class Invalidator extends AbstractInvalidator
{
    const TYPE_PURCHASE_ORDER_ITEM = 'purchaseOrderItem';
    const TYPE_PURCHASE_ORDER = 'purchaseOrder';
    protected $uriTypeMap = [
        self::TYPE_PURCHASE_ORDER_ITEM => '/purchaseOrderItem',
        self::TYPE_PURCHASE_ORDER => '/purchaseOrder',
    ];

    public function invalidatePurchaseOrderForItem(Item $item)
    {
        try {
            $this->purgeResources([
                new Resource(static::TYPE_PURCHASE_ORDER, $item->getPurchaseOrderId()),
            ]);
        } catch (\Exception $ex) {
            // Ignore errors
        }
    }

    protected function getUriForType($type)
    {
        return $this->uriTypeMap[$type];
    }
}

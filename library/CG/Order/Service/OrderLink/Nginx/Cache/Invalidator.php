<?php
namespace CG\Order\Service\OrderLink\Nginx\Cache;

use CG\CGLib\Nginx\Cache\AbstractInvalidator;
use CG\Order\Shared\OrderLink\Entity as OrderLink;

class Invalidator extends AbstractInvalidator
{
    
    const TYPE_ORDER_LINK = 'orderLink';
    const TYPE_ORDER = 'order';
    protected $uriTypeMap = [
        self::TYPE_ORDER_LINK => '/orderLink',
        self::TYPE_ORDER => '/order',
    ];

    public function invalidateOrdersForOrderLink(OrderLink $orderLink)
    {
        foreach ($orderLink->getOrderIds() as $orderId) {
            $this->invalidateOrderById($orderId);
        }
    }

    protected function invalidateOrderById($orderId)
    {
        try {
            $this->purgeResource(static::TYPE_ORDER, $orderId);
        } catch (\Exception $ex) {
            // Ignore errors
        }
    }

    protected function getUriForType($type)
    {
        return $this->uriTypeMap[$type];
    }
}

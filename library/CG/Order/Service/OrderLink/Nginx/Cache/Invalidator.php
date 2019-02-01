<?php
namespace CG\Order\Service\OrderLink\Nginx\Cache;

use CG\CGLib\Nginx\Cache\AbstractInvalidator;
use CG\CGLib\Nginx\Cache\Resource;
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
        $resources = [];
        foreach ($orderLink->getOrderIds() as $orderId) {
            $resources[] = new Resource(static::TYPE_ORDER, $orderId);
        }

        try {
            $this->purgeResources($resources);
        } catch (\Exception $ex) {
            // Ignore errors
        }
    }

    protected function getUriForType($type)
    {
        return $this->uriTypeMap[$type];
    }
}

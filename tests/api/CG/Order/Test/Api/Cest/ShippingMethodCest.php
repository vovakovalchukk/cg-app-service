<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Order\Test\Api\Page\ShippingMethodPage;

class ShippingMethodCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return ShippingMethodPage::class;
    }
}
 
<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\ShippingMethodEntityPage;

class ShippingMethodEntityCest
{
    use EntityTrait;

    public function getPageClass()
    {
        return ShippingMethodEntityPage::class;
    }
}
 
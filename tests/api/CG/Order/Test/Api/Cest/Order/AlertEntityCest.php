<?php
namespace CG\Order\Test\Api\Cest\Order;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\Order\AlertEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class AlertEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return AlertEntityPage::class;
    }
}
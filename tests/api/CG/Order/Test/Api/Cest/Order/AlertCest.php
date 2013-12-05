<?php
namespace CG\Order\Test\Api\Cest\Order;

use CG\Order\Test\Api\Page\Order\AlertPage;
use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;

class AlertCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return AlertPage::class;
    }
}
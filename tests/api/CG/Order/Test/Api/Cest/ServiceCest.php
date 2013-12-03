<?php
namespace CG\Order\Test\Api\Cest;

use CG\Order\Test\Api\Page\ServicePage;
use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;

class ServiceCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return ServicePage::class;
    }
}
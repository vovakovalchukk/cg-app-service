<?php
namespace CG\Order\Test\Api\Cest;

use CG\Order\Test\Api\Page\RootPage;
use CG\Codeception\Cest\Rest\EndpointsTrait;

class RootCest
{
    use EndpointsTrait;

    protected function getPageClass(){
        return RootPage::class;
    }
}
<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\OrderEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class OrderEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return OrderEntityPage::class;
    }
}
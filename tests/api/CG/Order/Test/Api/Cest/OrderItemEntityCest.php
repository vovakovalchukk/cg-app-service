<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\OrderItemEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class OrderItemEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return OrderItemEntityPage::class;
    }
}
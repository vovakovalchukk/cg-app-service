<?php
namespace CG\Order\Test\Api\Cest;

use CG\Order\Test\Api\Page\OrderItemPage;
use CG\Codeception\Cest\Rest\CollectionTrait;

class OrderItemCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return OrderItemPage::class;
    }
}
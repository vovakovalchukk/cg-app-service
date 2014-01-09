<?php
namespace CG\Order\Test\Api\Cest;

use CG\Order\Test\Api\Page\OrderBatchPage;
use CG\Codeception\Cest\Rest\CollectionTrait;

class OrderCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return OrderBatchPage::class;
    }
}
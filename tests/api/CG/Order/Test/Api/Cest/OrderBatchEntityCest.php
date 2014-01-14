<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\OrderBatchEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class OrderBatchEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return OrderBatchEntityPage::class;
    }
}
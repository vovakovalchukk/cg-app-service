<?php
namespace CG\Order\Test\Api\Cest;

use CG\Order\Test\Api\Page\OrderTagPage;
use CG\Codeception\Cest\Rest\CollectionTrait;

class OrderTagCest
{
    public function getPageClass()
    {
        return OrderTagPage::class;
    }
}
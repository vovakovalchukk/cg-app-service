<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\OrderTagEntityPage;

class OrderTagEntityCest
{
    use EntityTrait;

    public function getPageClass()
    {
        return OrderTagEntityPage::class;
    }
}
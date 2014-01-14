<?php
namespace CG\Order\Test\Api\Cest;


use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\OrderTagEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class OrderTagEntityCest
{
    use EntityTrait, EntityETagTrait;

    public function getPageClass()
    {
        return OrderTagEntityPage::class;
    }
}
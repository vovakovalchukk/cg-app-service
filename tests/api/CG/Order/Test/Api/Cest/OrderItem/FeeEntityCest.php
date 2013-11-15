<?php
namespace CG\Order\Test\Api\Cest\OrderItem;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\OrderItem\FeeEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class FeeEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return FeeEntityPage::class;
    }
}
<?php
namespace CG\Order\Test\Api\Cest\OrderItem;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\OrderItem\GiftWrapEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class GiftWrapEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return GiftWrapEntityPage::class;
    }
}
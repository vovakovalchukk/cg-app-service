<?php
namespace CG\Order\Test\Api\Cest\OrderItem;

use CG\Order\Test\Api\Page\OrderItem\GiftWrapPage;
use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;

class GiftWrapCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return GiftWrapPage::class;
    }
}
<?php
namespace CG\Order\Test\Api\Cest\Order;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\Order\TrackingEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class TrackingEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return TrackingEntityPage::class;
    }
}
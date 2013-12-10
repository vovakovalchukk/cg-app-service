<?php
namespace CG\Order\Test\Api\Cest\Service;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\Service\SubscribedEventEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;
use ApiGuy;

class SubscribedEventEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return SubscribedEventEntityPage::class;
    }
}
<?php
namespace CG\Order\Test\Api\Cest\Service;

use CG\Order\Test\Api\Page\Service\SubscribedEventPage;
use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;

class SubscribedEventCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return SubscribedEventPage::class;
    }
}
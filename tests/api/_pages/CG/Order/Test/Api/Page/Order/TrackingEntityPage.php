<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Order\Test\Api\Page\Order\TrackingPage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class TrackingEntityPage extends TrackingPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return TrackingPage::class;
    }

    public static function notAllowedMethods()
    {
        return [
                static::POST => static::POST
        ];
    }
}
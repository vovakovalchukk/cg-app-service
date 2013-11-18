<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Order\Test\Api\Page\Order\AlertPage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class AlertEntityPage extends AlertPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return AlertPage::class;
    }

    public static function notAllowedMethods()
    {
        return [
                static::POST => static::POST
        ];
    }
}
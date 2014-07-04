<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class ShippingMethodEntityPage extends ShippingMethodPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return ShippingMethodPage::class;
    }

    public static function notAllowedMethods()
    {
        return [
            static::POST => static::POST,
            static::PUT  => static::PUT,
            static::DELETE  => static::DELETE
        ];
    }
}
 
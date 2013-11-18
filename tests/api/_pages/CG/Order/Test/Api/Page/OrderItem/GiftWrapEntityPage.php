<?php
namespace CG\Order\Test\Api\Page\OrderItem;

use CG\Order\Test\Api\Page\OrderItem\GiftWrapPage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class GiftWrapEntityPage extends GiftWrapPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return GiftWrapPage::class;
    }

    public static function notAllowedMethods()
    {
        return [
                static::POST => static::POST,
                static::DELETE => static::DELETE
        ];
    }
}
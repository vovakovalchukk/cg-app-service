<?php
namespace CG\Order\Test\Api\Page;

use CG\Order\Test\Api\Page\OrderItemPage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class OrderItemEntityPage extends OrderItemPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage(){
        return OrderItemPage::class;
    }

    public static function notAllowedMethods(){
        return [
                static::POST => static::POST,
                static::DELETE => static::DELETE
        ];
    }
}
<?php
namespace CG\Order\Test\Api\Page;

use CG\Order\Test\Api\Page\OrderPage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class OrderEntityPage extends OrderPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage(){
        return OrderPage::class;
    }

    public static function notAllowedMethods(){
        return [
                static::POST => static::POST
        ];
    }
}
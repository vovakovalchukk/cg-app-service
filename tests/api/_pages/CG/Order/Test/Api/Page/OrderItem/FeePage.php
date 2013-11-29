<?php
namespace CG\Order\Test\Api\Page\OrderItem;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderItemEntityPage;

class FeePage extends OrderItemEntityPage
{
    use CollectionPageTrait;
    const URL = "/fee";
    const EMBEDDED_RESOURCE = "fee";
    const PRIMARY_ID = "1";
    const SECONDARY_ID = "2";

    public static function getUrl()
    {
        return parent::getEntityUrl() . self::URL;
    }

    static public function notAllowedMethods()
    {
        return [
            static::PUT => static::PUT,
            static::DELETE => static::DELETE
        ];
    }

    public static function getTestCollection()
    {

        return [
                [
                    "orderItemId" => "1411-11",
                    "name" => "eBayFee",
                    "amount" => 1.99,
                    "id" => 1
                ],
                [
                    "orderItemId" => "1411-11",
                    "name" => "eBayFee",
                    "amount" => 2.99,
                    "id" => 2
                ],
                [
                    "orderItemId" => "1411-11",
                    "name" => "eBayFee",
                    "amount" => 3.99,
                    "id" => 3
                ],
                [
                    "orderItemId" => "1411-11",
                    "name" => "eBayFee",
                    "amount" => 4.99,
                    "id" => 4
                ],
                [
                    "orderItemId" => "1411-12",
                    "name" => "eBayFee",
                    "amount" => 5.99,
                    "id" => 5
                ],
                [
                    "orderItemId" => "1411-11",
                    "name" => "eBayFee",
                    "amount" => 6.99,
                    "id" => 6
                ],
        ];
    }

    public static function getRequiredEntityFields()
    {
        return ["name", "amount"];
    }

    public static function getInvalidEntityData()
    {
        return [
                "name" => [],
                "amount" => []
               ];
    }

    public static function getInvalidEntityFields()
    {
        return ["name", "amount"];
    }

    public static function getParentIdField()
    {
        return "orderItemId";
    }
}
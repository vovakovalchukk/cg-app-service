<?php
namespace CG\Order\Test\Api\Page\OrderItem;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderItemEntityPage;

class FeePage extends OrderItemEntityPage
{
    use CollectionPageTrait;
    const URL = "/fee";
    const EMBEDDED_RESOURCE = "fee";

    public static function getUrl()
    {
        return self::URL;
    }

    static public function notAllowedMethods()
    {
        return [
            static::GET => static::GET,
            static::POST => static::POST,
            static::PUT => static::PUT,
            static::DELETE => static::DELETE
        ];
    }

    public static function getTestCollection()
    {

        return [
                ["orderItemId" => "1411-11",
                 "fee" => [
                            ["name" => "eBayFee",
                            "amount" => "1.99"],
                            ["name" => "PayPal Fee",
                            "amount" => "12.99"]
                          ]
                ],
                ["orderItemId" => "1411-12",
                "fee" => [
                            ["name" => "eBayFee",
                            "amount" => "2.99"],
                            ["name" => "PayPal Fee",
                            "amount" => "22.99"]
                         ]
                ],
                ["orderItemId" => "1411-13",
                "fee" => [
                            ["name" => "eBayFee",
                            "amount" => "3.99"],
                            ["name" => "PayPal Fee",
                            "amount" => "32.99"]
                         ]
                ],
                ["orderItemId" => "1414-44",
                "fee" => [
                            ["name" => "eBayFee",
                            "amount" => "4.99"],
                            ["name" => "PayPal Fee",
                            "amount" => "42.99"]
                         ]
                ],
                ["orderItemId" => "1414-45",
                "fee" => [
                            ["name" => "eBayFee",
                            "amount" => "5.99"],
                            ["name" => "PayPal Fee",
                            "amount" => "52.99"]
                         ]
                ]
               ];
    }

    public static function getRequiredEntityFields()
    {
        return ["orderItemId", "fee" => ["name", "amount"]];
    }

    public static function getInvalidEntityData()
    {
        return [
                "orderItemId" => [],
                "fee" => [
                            ["name" => [],
                            "amount" => []
                            ]
                         ]
               ];
    }

    public static function getInvalidEntityFields()
    {
        return ["orderItemId", "fee" => ["name", "amount"]];
    }
}
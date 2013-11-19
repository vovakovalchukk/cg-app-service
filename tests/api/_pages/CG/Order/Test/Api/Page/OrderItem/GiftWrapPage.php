<?php
namespace CG\Order\Test\Api\Page\OrderItem;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderItemEntityPage;

class GiftWrapPage extends OrderItemEntityPage
{
    use CollectionPageTrait;
    const URL = "/giftWrap";
    const EMBEDDED_RESOURCE = "giftWrap";

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
                 "giftWrapType" => "Standard",
                 "giftWrapMessage" => "Wrap Message 1",
                 "giftWrapPrice" => 1.99,
                 "giftWrapTaxPercentage" => 0.1
                ],
                ["orderItemId" => "1411-12",
                "giftWrapType" => "Standard",
                "giftWrapMessage" => "Wrap Message 2",
                "giftWrapPrice" => 2.99,
                "giftWrapTaxPercentage" => 0.2
                ],
                ["orderItemId" => "1411-13",
                "giftWrapType" => "Standard",
                "giftWrapMessage" => "Wrap Message 3",
                "giftWrapPrice" => 3.99,
                "giftWrapTaxPercentage" => 0.3
                ],
                ["orderItemId" => "1414-44",
                "giftWrapType" => "Standard",
                "giftWrapMessage" => "Wrap Message 4",
                "giftWrapPrice" => 4.99,
                "giftWrapTaxPercentage" => 0.4
                ],
                ["orderItemId" => "1414-45",
                "giftWrapType" => "Standard",
                "giftWrapMessage" => "Wrap Message 5",
                "giftWrapPrice" => 5.99,
                "giftWrapTaxPercentage" => 0.5
                ],
               ];
    }

    public static function getRequiredEntityFields()
    {
        return ["orderItemId", "giftWrapType", "giftWrapMessage", "giftWrapPrice", "giftWrapTaxPercentage"];
    }

    public static function getInvalidEntityData()
    {
        return [
                "orderItemId" => [],
                "giftWrapType" => [],
                "giftWrapMessage" => [],
                "giftWrapPrice" => "ABC",
                "giftWrapTaxPercentage" => "ABC"
               ];
    }

    public static function getInvalidEntityFields()
    {
        return ["orderItemId", "giftWrapType", "giftWrapMessage", "giftWrapPrice", "giftWrapTaxPercentage"];
    }

    public static function getParentIdField()
    {
        return "orderItemId";
    }
}
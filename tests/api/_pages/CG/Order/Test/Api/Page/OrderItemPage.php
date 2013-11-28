<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;

class OrderItemPage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/orderItem";
    const EMBEDDED_RESOURCE = "orderItem";
    const PRIMARY_ID = "1411-11";
    const SECONDARY_ID = "1411-12";

    public static function getUrl(){
        return self::URL;
    }

    public static function notAllowedMethods(){
        return [
                static::POST    => static::POST,
            static::GET    => static::GET,
            static::PUT    => static::PUT,
            static::DELETE    => static::DELETE
        ];
    }

    public static function getTestCollection()
    {
        return [
                ["id" => "1411-11",
                "orderId" => "1411-10",
                "accountId" => 1411,
                "itemName" => "Order Item 1",
                "individualItemPrice" => 1.99,
                "itemQuantity" => 10,
                "itemSku" => "test-sku-1",
                "itemTaxPercentage" => 0.1,
                "individualItemDiscountPrice" => 0.199,
                "itemVariationAttribute" => ["colour" => "red",
                                             "size" => "10cm"]
                ],
                ["id" => "1411-12",
                    "orderId" => "1411-10",
                    "accountId" => 1411,
                    "itemName" => "Order Item 2",
                    "individualItemPrice" => 2.99,
                    "itemQuantity" => 20,
                    "itemSku" => "test-sku-2",
                    "itemTaxPercentage" => 0.20,
                    "individualItemDiscountPrice" => 0.299,
                    "itemVariationAttribute" => ["colour" => "blue",
                                                 "size" => "20cm"]
                ],
                ["id" => "1411-13",
                    "orderId" => "1411-10",
                    "accountId" => 1411,
                    "itemName" => "Order Item 3",
                    "individualItemPrice" => 3.99,
                    "itemQuantity" => 30,
                    "itemSku" => "test-sku-3",
                    "itemTaxPercentage" => 0.3,
                    "individualItemDiscountPrice" => 0.399,
                    "itemVariationAttribute" => ["colour" => "yellow",
                                                 "size" => "30cm"]
                ],
                ["id" => "1411-44",
                    "orderId" => "1411-10",
                    "accountId" => 1411,
                    "itemName" => "Order Item-1",
                    "individualItemPrice" => 4.99,
                    "itemQuantity" => 40,
                    "itemSku" => "test-sku-4",
                    "itemTaxPercentage" => 0.4,
                    "individualItemDiscountPrice" => 0.499,
                    "itemVariationAttribute" => ["colour" => "green",
                                                 "size" => "40cm"]
                ],
                ["id" => "1411-45",
                    "orderId" => "1411-10",
                    "accountId" => 1411,
                    "itemName" => "Order Item-2",
                    "individualItemPrice" => 5.99,
                    "itemQuantity" => 50,
                    "itemSku" => "test-sku-5",
                    "itemTaxPercentage" => 0.5,
                    "individualItemDiscountPrice" => 0.599,
                    "itemVariationAttribute" => ["colour" => "red",
                                                "size" => "50cm"]
                ],
        ];
    }

    public static function getRequiredEntityFields()
    {
        return ["accountId",
                "itemName",
                "individualItemPrice",
                "itemQuantity",
                "itemSku",
                "itemTaxPercentage",
                "individualItemDiscountPrice",
                "itemVariationAttribute" => []
        ];
    }

    public static function getInvalidEntityData()
    {
        return ["accountId" => "ABC",
                "itemName" => [],
                "individualItemPrice" => "ABC",
                "itemQuantity" => "ABC",
                "itemSku" => [],
                "itemTaxPercentage" => "ABC",
                "individualItemDiscountPrice" => "ABC",
                "itemVariationAttribute" => "ABC"
        ];
    }

    public static function getInvalidEntityFields()
    {
        return ["accountId",
                "itemName",
                "individualItemPrice",
                "itemQuantity",
                "itemSku",
                "itemTaxPercentage",
                "individualItemDiscountPrice",
                "itemVariationAttribute" => []
        ];
    }

    public static function getParentIdField()
    {
        return "orderId";
    }
}
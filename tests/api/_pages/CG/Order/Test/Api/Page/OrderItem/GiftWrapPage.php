<?php
namespace CG\Order\Test\Api\Page\OrderItem;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderItemEntityPage;

class GiftWrapPage extends OrderItemEntityPage
{
    use CollectionPageTrait;
    const URL = "/giftWrap";
    const EMBEDDED_RESOURCE = "giftWrap";
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
                ["id" => 1,
                 "orderItemId" => "1411-11",
                 "giftWrapType" => "Standard",
                 "giftWrapMessage" => "Wrap Message 1",
                 "giftWrapPrice" => 1.99,
                 "giftWrapTaxPercentage" => 0.1,
                 "organisationUnitId" => 1
                ],
                ["id" => 2,
                 "orderItemId" => "1411-11",
                 "giftWrapType" => "Standard",
                 "giftWrapMessage" => "Wrap Message 2",
                 "giftWrapPrice" => 2.99,
                 "giftWrapTaxPercentage" => 0.2,
                 "organisationUnitId" => 1
                ],
                ["id" => 3,
                 "orderItemId" => "1411-11",
                 "giftWrapType" => "Standard",
                 "giftWrapMessage" => "Wrap Message 3",
                 "giftWrapPrice" => 3.99,
                 "giftWrapTaxPercentage" => 0.3,
                 "organisationUnitId" => 1
                ],
                ["id" => 4,
                 "orderItemId" => "1411-11",
                 "giftWrapType" => "Standard",
                 "giftWrapMessage" => "Wrap Message 4",
                 "giftWrapPrice" => 4.99,
                 "giftWrapTaxPercentage" => 0.4,
                 "organisationUnitId" => 1
                ],
                ["id" => 5,
                 "orderItemId" => "1411-12",
                 "giftWrapType" => "Standard",
                 "giftWrapMessage" => "Wrap Message 5",
                 "giftWrapPrice" => 5.99,
                 "giftWrapTaxPercentage" => 0.5,
                 "organisationUnitId" => 1
                ],
                ["id" => 6,
                    "orderItemId" => "1411-11",
                    "giftWrapType" => "Standard",
                    "giftWrapMessage" => "Wrap Message 6",
                    "giftWrapPrice" => 6.99,
                    "giftWrapTaxPercentage" => 0.6,
                    "organisationUnitId" => 1
                ]
               ];
    }

    public static function getRequiredEntityFields()
    {
        return ["giftWrapMessage"];
    }

    public static function getInvalidEntityData()
    {
        return [
                "giftWrapMessage" => [],
                "giftWrapPrice" => "ABC",
                "giftWrapTaxPercentage" => "ABC"
               ];
    }

    public static function getInvalidEntityFields()
    {
        return ["giftWrapMessage", "giftWrapPrice", "giftWrapTaxPercentage"];
    }

    public static function getParentIdField()
    {
        return "orderItemId";
    }
}
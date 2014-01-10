<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;

class UserPreferencePage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/userPreference";
    const EMBEDDED_RESOURCE = "userPreference";
    const PRIMARY_ID = "1";
    const SECONDARY_ID = "2";

    public static function getUrl()
    {
        return self::URL;
    }

    public static function notAllowedMethods()
    {
        return [
            static::POST    => static::POST,
            static::PUT    => static::PUT,
            static::DELETE => static::DELETE
        ];
    }

    public static function getTestCollection()
    {
        return [
            [
                "id" => "1",
                "preference" => [
                    "orderTable" => [
                        "tags",
                        "orderInformation",
                        "shippingService",
                        "dispatch"
                    ],
                    "other" => 1
                ]
            ],
            [
                "id" => "2",
                "preference" => [
                    "orderTable" => [
                        "orderInformation",
                        "shippingService",
                        "dispatch",
                        "tags"
                    ],
                    "other" => 2
                ]
            ],
            [
                "id" => "3",
                "preference" => [
                    "orderTable" => [
                        "tags",
                        "orderInformation",
                        "shippingService",
                        "dispatch",
                        "invoice"
                    ],
                    "other" => 3
                ]
            ],
            [
                "id" => "4",
                "preference" => [
                    "other" => 4
                ]
            ],[
                "id" => "5",
                "preference" => [
                    "orderTable" => [
                        "checkbox",
                        "orderInformation",
                        "shippingService",
                        "buyerName"
                    ]
                ]
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            "id",
            "preference"
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            "id" => [],
            "preference" => "ABC"
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            "preference",
            "id"
        ];
    }

    public static function getFilterFields()
    {
        return [];
    }
}
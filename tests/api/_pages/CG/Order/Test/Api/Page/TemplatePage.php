<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;

class TemplatePage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/template";
    const EMBEDDED_RESOURCE = "template";
    const PRIMARY_ID = "1";
    const SECONDARY_ID = "2";

    public static function getUrl()
    {
        return self::URL;
    }

    public static function notAllowedMethods()
    {
        return [
            static::PUT    => static::PUT,
            static::DELETE => static::DELETE
        ];
    }

    public static function getTestCollection()
    {
        return [
            [
                "id" => "1",
                "type" => "invoice",
                "organisationUnitId" => 1,
                "minHeight" => 100,
                "minWidth" => 200,
                "elements" => [
                    [
                        "templateType" => "Text",
                        "fontSize" => 12,
                        "fontFamily" => "CG\\Template\\FontFamily\\Courier",
                        "text" => "Random Text",
                        "fontColour" => "red"
                    ]
                ]
            ],
            [
                "id" => "2",
                "type" => "product",
                "organisationUnitId" => 2,
                "minHeight" => 200,
                "minWidth" => 300,
                "elements" => [
                    [
                        "templateType" => "SellerAddress",
                        "fontSize" => 14,
                        "fontFamily" => "CG\\Template\\FontFamily\\Helvetica",
                        "fontColour" => "blue"
                    ]
                ]
            ],
            [
                "id" => "3",
                "type" => "ebay",
                "organisationUnitId" => 3,
                "minHeight" => 300,
                "minWidth" => 400,
                "elements" => [
                    [
                        "templateType" => "DeliveryAddress",
                        "fontSize" => 16,
                        "fontFamily" => "CG\\Template\\FontFamily\\Symbol",
                        "fontColour" => "green"
                    ]
                ]
            ],
            [
                "id" => "4",
                "type" => "amazon",
                "organisationUnitId" => 4,
                "minHeight" => 400,
                "minWidth" => 500,
                "elements" => [
                    [
                        "templateType" => "OrderTable"
                    ]
                ]
            ],
            [
                "id" => "5",
                "type" => "invoice",
                "organisationUnitId" => 5,
                "minHeight" => 500,
                "minWidth" => 600,
                "elements" => [
                    [
                        "templateType" => "Image",
                        "source" => "data://image/jpeg;base64,base64encodedImageData",
                        "format" => "jpeg"
                    ]
                ]
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            "type",
            "organisationUnitId",
            "minWidth",
            "minHeight",
            "elements"
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            "id" => [],
            "type" => [],
            "organisationUnitId" => [],
            "elements" => "ABC",
            "minWidth" => [],
            "minHeight" => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            "id",
            "type",
            "organisationUnitId",
            "minWidth",
            "minHeight",
            "elements"
        ];
    }

    public static function getFilterFields()
    {
        return [
            "id" => [],
            "organisationUnitId" => [],
            "type" => []
        ];
    }
}
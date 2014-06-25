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
                "elements" => [
                    [
                        "type" => "Text",
                        "fontSize" => 12,
                        "fontFamily" => "Courier",
                        "text" => "Random Text",
                        "fontColour" => "red"
                    ]
                ],
                "name" => "name1",
                "paperPage" => [
                    "height" => 100,
                    "width" => 400,
                    "paperType" => 'class c'
                ]
            ],
            [
                "id" => "2",
                "type" => "product",
                "organisationUnitId" => 2,
                "elements" => [
                    [
                        "type" => "SellerAddress",
                        "fontSize" => 14,
                        "fontFamily" => "Helvetica",
                        "fontColour" => "blue"
                    ]
                ],
                "name" => "name2",
                "paperPage" => [
                    "height" => 200,
                    "width" => 800,
                    "paperType" => 'class c'
                ]
            ],
            [
                "id" => "3",
                "type" => "ebay",
                "organisationUnitId" => 3,
                "elements" => [
                    [
                        "type" => "DeliveryAddress",
                        "fontSize" => 16,
                        "fontFamily" => "Helvetica",
                        "fontColour" => "green"
                    ]
                ],
                "name" => "name3",
                "paperPage" => [
                    "height" => 300,
                    "width" => 1200,
                    "paperType" => 'class c'
                ]
            ],
            [
                "id" => "4",
                "type" => "amazon",
                "organisationUnitId" => 4,
                "elements" => [
                    [
                        "type" => "OrderTable"
                    ]
                ],
                "name" => "name4",
                "paperPage" => [
                    "height" => 400,
                    "width" => 1600,
                    "paperType" => 'class c'
                ]
            ],
            [
                "id" => "5",
                "type" => "invoice",
                "organisationUnitId" => 5,
                "elements" => [
                    [
                        "type" => "Image",
                        "source" => "data://image/jpeg;base64,base64encodedImageData",
                        "format" => "jpeg"
                    ]
                ],
                "name" => "name5",
                "paperPage" => [
                    "height" => 500,
                    "width" => 2000,
                    "paperType" => 'class c'
                ]
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            "type",
            "organisationUnitId",
            "elements",
            "name"
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            "id" => [],
            "type" => [],
            "organisationUnitId" => [],
            "elements" => "ABC",
            "name" => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            "id",
            "type",
            "organisationUnitId",
            "elements",
            "name"
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
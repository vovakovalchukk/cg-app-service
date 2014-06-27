<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;

class OrderTagPage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/orderTag";
    const EMBEDDED_RESOURCE = "orderTag";
    const PRIMARY_ID = "1411-10-tag1";
    const SECONDARY_ID = "1411-10-tag2";

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
                "id" => "1411-10-tag1",
                "orderId" => "1411-10",
                "organisationUnitId" => 1,
                "tag" => "tag1"
            ],
            [
                "id" => "1411-10-tag2",
                "orderId" => "1411-10",
                "organisationUnitId" => 1,
                "tag" => "tag2"
            ],
            [
                "id" => "1411-10-tag5",
                "orderId" => "1411-10",
                "organisationUnitId" => 1,
                "tag" => "tag5"
            ],
            [
                "id" => "1413-30-tag4",
                "orderId" => "1413-30",
                "organisationUnitId" => 3,
                "tag" => "tag4"
            ],
            [
                "id" => "1412-20-tag3",
                "orderId" => "1412-20",
                "organisationUnitId" => 2,
                "tag" => "tag3"
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            "organisationUnitId",
            "tag",
            "id",
            "orderId"
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            "name" => [],
            "organisationUnitId" => [],
            "id" => [],
            "orderId" => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            "orderId",
            "organisationUnitId",
            "name",
            "id"
        ];
    }


    public static function getFilterFields()
    {
        return [
            "tag" => [],
            "organisationUnitId" => []
        ];
    }
}
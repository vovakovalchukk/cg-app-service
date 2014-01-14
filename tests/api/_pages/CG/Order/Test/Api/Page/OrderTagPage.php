<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;

class OrderTagPage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/orderTag";
    const EMBEDDED_RESOURCE = "orderTag";
    const PRIMARY_ID = "1-Tag1";
    const SECONDARY_ID = "2-Tag2";

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
                "id" => "1-tag1",
                "organisationUnitId" => 1,
                "name" => "tag1"
            ],
            [
                "id" => "1-tag2",
                "organisationUnitId" => 1,
                "name" => "tag2"
            ],
            [
                "id" => "1-tag3",
                "organisationUnitId" => 1,
                "name" => "tag3"
            ],
            [
                "id" => "1-tag4",
                "organisationUnitId" => 1,
                "name" => "tag4"
            ],
            [
                "id" => "2-tag4",
                "organisationUnitId" => 2,
                "name" => "tag5"
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            "organisationUnitId",
            "name",
            "id"
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            "name" => [],
            "organisationUnitId" => [],
            "id" => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            "organisationUnitId",
            "name",
            "id"
        ];
    }


    public static function getFilterFields()
    {
        return [
            "name", "organisationUnitId" => []
        ];
    }
}
<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;

class OrderBatchPage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/orderBatch";
    const EMBEDDED_RESOURCE = "orderBatch";
    const PRIMARY_ID = "1-1";
    const SECONDARY_ID = "1-2";

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
            ["name" => "1", "organisationUnitId" => 1, "id" => "1-1", "active" => 1],
            ["name" => "2", "organisationUnitId" => 1, "id" => "1-2", "active" => 1],
            ["name" => "3", "organisationUnitId" => 1, "id" => "1-3", "active" => 1],
            ["name" => "4", "organisationUnitId" => 1, "id" => "1-4", "active" => 1],
            ["name" => "5", "organisationUnitId" => 1, "id" => "1-5", "active" => 0],
            ["name" => "1", "organisationUnitId" => 2, "id" => "2-1", "active" => 1]
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
            "id" => [],
            "active" => []
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
            "active", "organisationUnitId" => []
        ];
    }
}
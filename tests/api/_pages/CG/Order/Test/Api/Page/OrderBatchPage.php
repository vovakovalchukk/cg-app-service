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
            static::PUT    => static::PUT,
            static::DELETE => static::DELETE
        ];
    }

    public static function getTestCollection()
    {
        return [
            ["name" => "1", "organisationUnitId" => 1, "id" => "1-1", "active" => true],
            ["name" => "2", "organisationUnitId" => 1, "id" => "1-2", "active" => true],
            ["name" => "3", "organisationUnitId" => 1, "id" => "1-3", "active" => true],
            ["name" => "4", "organisationUnitId" => 1, "id" => "1-4", "active" => true],
            ["name" => "5", "organisationUnitId" => 1, "id" => "1-5", "active" => false],
            ["name" => "1", "organisationUnitId" => 2, "id" => "2-1", "active" => true]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            "organisationUnitId",
            "active"
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
            "active"
        ];
    }

    public static function getFilterFields()
    {
        return [
            "active"
        ];
    }
}
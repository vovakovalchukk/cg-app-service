<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderEntityPage;

class TrackingPage extends OrderEntityPage
{
    use CollectionPageTrait;
    const URL = "/tracking";
    const EMBEDDED_RESOURCE = "tracking";
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
                [
                 "id" => 1,
                 "orderId" => "1411-10",
                 "userId" => 1,
                 "number" => "1231",
                 "carrier" => "carrier 1",
                 "organisationUnitId" => 1,
                 "timestamp" => "2013-10-10 01:00:00"
                ],
                [
                 "id" => 2,
                 "orderId" => "1411-10",
                 "userId" => 2,
                 "number" => "1232",
                 "carrier" => "carrier 2",
                 "organisationUnitId" => 1,
                 "timestamp" => "2013-10-10 02:00:00"
                ],
                [
                 "id" => 3,
                 "orderId" => "1411-10",
                 "userId" => 3,
                 "number" => "1233",
                 "carrier" => "carrier 3",
                 "organisationUnitId" => 1,
                 "timestamp" => "2013-10-10 03:00:00"
                ],
                [
                 "id" => 4,
                 "orderId" => "1411-10",
                 "userId" => 4,
                 "number" => "1234",
                 "carrier" => "carrier 4",
                 "organisationUnitId" => 1,
                 "timestamp" => "2013-10-10 04:00:00"
                ],
                [
                 "id" => 5,
                 "orderId" => "1412-20",
                 "userId" => 5,
                 "number" => "1235",
                 "carrier" => "carrier 5",
                 "organisationUnitId" => 2,
                 "timestamp" => "2013-10-10 05:00:00"
                ],
                [
                    "id" => 6,
                    "orderId" => "1411-10",
                    "userId" => 6,
                    "number" => "1236",
                    "carrier" => "carrier 6",
                    "organisationUnitId" => 1,
                    "timestamp" => "2013-10-10 06:00:00"
                ],
               ];
    }

    public static function getRequiredEntityFields()
    {
        return ["userId", "number", "carrier", "timestamp", "organisationUnitId"];
    }

    public static function getInvalidEntityData()
    {
        return [
                "userId" => "ABC",
                "number" => [],
                "carrier" => [],
                "timestamp" => [],
                "organisationUnitId" => []
               ];
    }

    public static function getInvalidEntityFields()
    {
        return ["userId", "number", "carrier", "timestamp", "organisationUnitId"];
    }

    public static function getParentIdField()
    {
        return "orderId";
    }
}
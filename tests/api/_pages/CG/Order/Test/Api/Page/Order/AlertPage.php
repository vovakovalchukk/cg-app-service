<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderEntityPage;

class AlertPage extends OrderEntityPage
{
    use CollectionPageTrait;
    const URL = "/alert";
    const EMBEDDED_RESOURCE = "alert";
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
                 "alert" => "alert 1",
                 "organisationUnitId" => 1,
                 "timestamp" => "2013-10-10 01:00:00"
                ],
                [
                 "id" => 2,
                 "orderId" => "1411-10",
                 "userId" => 2,
                 "alert" => "alert 2",
                 "organisationUnitId" => 1,
                 "timestamp" => "2013-10-10 02:00:00"
                ],
                [
                 "id" => 3,
                 "orderId" => "1411-10",
                 "userId" => 3,
                 "alert" => "alert 3",
                 "organisationUnitId" => 1,
                 "timestamp" => "2013-10-10 03:00:00"
                ],
                [
                 "id" => 4,
                 "orderId" => "1411-10",
                 "userId" => 4,
                 "alert" => "alert 4",
                 "organisationUnitId" => 1,
                 "timestamp" => "2013-10-10 04:00:00"
                ],
                [
                 "id" => 5,
                 "orderId" => "1412-20",
                 "userId" => 5,
                 "alert" => "alert 5",
                 "organisationUnitId" => 2,
                 "timestamp" => "2013-10-10 05:00:00"
                ],
                [
                    "id" => 6,
                    "orderId" => "1411-10",
                    "userId" => 6,
                    "alert" => "alert 6",
                    "organisationUnitId" => 1,
                    "timestamp" => "2013-10-10 06:00:00"
                ]
               ];
    }

    public static function getRequiredEntityFields()
    {
        return ["userId", "alert", "timestamp", "organisationUnitId"];
    }

    public static function getInvalidEntityData()
    {
        return [
                "userId" => "ABC",
                "alert" => [],
                "timestamp" => [],
                "organisationUnitId" => []
               ];
    }

    public static function getInvalidEntityFields()
    {
        return ["userId", "alert", "timestamp", "organisationUnitId"];
    }

    public static function getParentIdField()
    {
        return "orderId";
    }
}
<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderEntityPage;

class TrackingPage extends OrderEntityPage
{
    use CollectionPageTrait;
    const URL = "/tracking";
    const EMBEDDED_RESOURCE = "tracking";

    public static function getUrl()
    {
        return self::URL;
    }

    static public function notAllowedMethods()
    {
        return [
                static::GET => static::GET,
                static::POST => static::POST,
                static::PUT => static::PUT,
                static::DELETE => static::DELETE
        ];
    }

    public static function getTestCollection()
    {
        return [
                ["orderId" => "1411-10",
                 "userId" => 1,
                 "number" => "1231",
                 "carrier" => "carrier 1",
                 "timestamp" => "2013-10-10 01:00:00"
                ],
                ["orderId" => "1411-20",
                 "userId" => 1,
                 "number" => "1232",
                 "carrier" => "carrier 2",
                 "timestamp" => "2013-10-10 02:00:00"
                ],
                ["orderId" => "1411-30",
                 "userId" => 1,
                 "number" => "1233",
                 "carrier" => "carrier 3",
                 "timestamp" => "2013-10-10 03:00:00"
                ],
                ["orderId" => "1414-40",
                 "userId" => 4,
                 "number" => "1234",
                 "carrier" => "carrier 4",
                 "timestamp" => "2013-10-10 04:00:00"
                ],
                ["orderId" => "1415-50",
                 "userId" => 5,
                 "number" => "1235",
                 "carrier" => "carrier 5",
                 "timestamp" => "2013-10-10 05:00:00"
                ],
               ];
    }

    public static function getRequiredEntityFields()
    {
        return ["orderId", "userId", "number", "carrier", "timestamp"];
    }

    public static function getInvalidEntityData()
    {
        return [
                "orderId" => [],
                "userId" => "ABC",
                "number" => [],
                "carrier" => [],
                "timestamp" => []
               ];
    }

    public static function getInvalidEntityFields()
    {
        return ["orderId", "userId", "number", "carrier", "timestamp"];
    }
}
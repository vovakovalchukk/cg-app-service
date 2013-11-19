<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderEntityPage;

class NotePage extends OrderEntityPage
{
    use CollectionPageTrait;
    const URL = "/note";
    const EMBEDDED_RESOURCE = "note";

    public static function getUrl()
    {
        return self::URL;
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
                ["id" => 1,
                 "orderId" => "1411-10",
                 "userId" => 1,
                 "note" => "Note 1",
                 "timestamp" => "2013-10-10 01:00:00"
                ],
                ["id" => 2,
                 "orderId" => "1411-20",
                 "userId" => 1,
                 "note" => "Note 2",
                 "timestamp" => "2013-10-10 02:00:00"
                ],
                ["id" => 3,
                 "orderId" => "1411-30",
                 "userId" => 1,
                 "note" => "Note 3",
                 "timestamp" => "2013-10-10 03:00:00"
                ],
                ["id" => 4,
                 "orderId" => "1414-40",
                 "userId" => 4,
                 "note" => "Note 4",
                 "timestamp" => "2013-10-10 04:00:00"
                ],
                ["id" => 5,
                 "orderId" => "1415-50",
                 "userId" => 5,
                 "note" => "Note 5",
                 "timestamp" => "2013-10-10 05:00:00"
                ],
               ];
    }

    public static function getRequiredEntityFields()
    {
        return ["orderId", "userId", "note", "timestamp"];
    }

    public static function getInvalidEntityData()
    {
        return [
                "orderId" => [],
                "userId" => "ABC",
                "note" => [],
                "timestamp" => []
               ];
    }

    public static function getInvalidEntityFields()
    {
        return ["orderId", "userId", "note", "timestamp"];
    }

    public static function getParentIdField()
    {
        return "orderId";
    }
}
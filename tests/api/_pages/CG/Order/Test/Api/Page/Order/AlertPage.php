<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderEntityPage;

class AlertPage extends OrderEntityPage
{
    use CollectionPageTrait;
    const URL = "/alert";
    const EMBEDDED_RESOURCE = "alert";

    public static function getUrl(){
        return parent::getEntityUrl() . self::URL;
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
                 "alert" => "alert 1",
                 "timestamp" => "2013-10-10 01:00:00"
                ],
                ["orderId" => "1411-20",
                 "userId" => 1,
                 "alert" => "alert 2",
                 "timestamp" => "2013-10-10 02:00:00"
                ],
                ["orderId" => "1411-30",
                 "userId" => 1,
                 "alert" => "alert 3",
                 "timestamp" => "2013-10-10 03:00:00"
                ],
                ["orderId" => "1414-40",
                 "userId" => 4,
                 "alert" => "alert 4",
                 "timestamp" => "2013-10-10 04:00:00"
                ],
                ["orderId" => "1415-50",
                 "userId" => 5,
                 "alert" => "alert 5",
                 "timestamp" => "2013-10-10 05:00:00"
                ],
               ];
    }

    public static function getRequiredEntityFields()
    {
        return ["orderId", "userId", "alert", "timestamp"];
    }

    public static function getInvalidEntityData()
    {
        return [
                "orderId" => [],
                "userId" => "ABC",
                "alert" => [],
                "timestamp" => []
               ];
    }

    public static function getInvalidEntityFields()
    {
        return ["orderId", "userId", "alert", "timestamp"];
    }
}
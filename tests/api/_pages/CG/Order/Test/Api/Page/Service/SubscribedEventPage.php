<?php
namespace CG\Order\Test\Api\Page\Service;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\ServiceEntityPage;

class SubscribedEventPage extends ServiceEntityPage
{
    use CollectionPageTrait;
    const URL = "/subscribedEvent";
    const EMBEDDED_RESOURCE = "subscribedEvent";

    public static function getUrl()
    {
        return parent::getEntityUrl() . self::URL;
    }

    static public function notAllowedMethods()
    {
        return [
            static::PUT => static::PUT,
            static::DELETE => static::DELETE,
        ];
    }

    public static function getTestCollection()
    {

        return [
                [
                    "type" => "type1",
                    "instances" => "instance1",
                    "endpoint" => "endpoint1"
                ],
                [
                    "type" => "type2",
                    "instances" => "instance2",
                    "endpoint" => "endpoint2"
                ],
                [
                    "type" => "type3",
                    "instances" => "instance3",
                    "endpoint" => "endpoint3"
                ],
                [
                    "type" => "type4",
                    "instances" => "instance4",
                    "endpoint" => "endpoint4"
                ],
                [
                    "type" => "type5",
                    "instances" => "instance5",
                    "endpoint" => "endpoint5"
                ]
               ];
    }

    public static function getRequiredEntityFields()
    {
        return ["type", "instances", "endpoint"];
    }

    public static function getInvalidEntityData()
    {
        return [
            "type" => [],
            "instances" => [],
            "endpoint" => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return ["type", "instances", "endpoint"];
    }

//    public static function getParentIdField()
//    {
//        return "orderItemId";
//    }
}
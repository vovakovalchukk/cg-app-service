<?php
namespace CG\Order\Test\Api\Page\Service;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\ServiceEntityPage;

class SubscribedEventPage extends ServiceEntityPage
{
    use CollectionPageTrait;
    const URL = "/subscribedEvent";
    const EMBEDDED_RESOURCE = "subscribedEvent";
    const PRIMARY_ID = "type1";
    const SECONDARY_ID = "type2";

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
                    "endpoint" => "http://example1.com"
                ],
                [
                    "type" => "type2",
                    "instances" => "instance2",
                    "endpoint" => "http://example2.com"
                ],
                [
                    "type" => "type3",
                    "instances" => "instance3",
                    "endpoint" => "http://example3.com"
                ],
                [
                    "type" => "type4",
                    "instances" => "instance4",
                    "endpoint" => "http://example4.com"
                ],
                [
                    "type" => "type5",
                    "instances" => "instance5",
                    "endpoint" => "http://example5.com"
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

    public static function getParentIdField()
    {
        return "type";
    }
}
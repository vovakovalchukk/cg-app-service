<?php
namespace CG\Order\Test\Api\Page\Service;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\ServiceEntityPage;

class SubscribedEventPage extends ServiceEntityPage
{
    use CollectionPageTrait;
    const URL = "/event";
    const EMBEDDED_RESOURCE = "event";
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
            static::DELETE => static::DELETE,
        ];
    }

    public static function getTestCollection()
    {

        return [
                [
                    "id" => 1,
                    "serviceId" => 1,
                    "type" => "type1",
                    "instances" => 1,
                    "endpoint" => "http://example1.com"
                ],
                [
                    "id" => 2,
                    "serviceId" => 1,
                    "type" => "type2",
                    "instances" => 2,
                    "endpoint" => "http://example2.com"
                ],
                [
                    "id" => 3,
                    "serviceId" => 1,
                    "type" => "type3",
                    "instances" => 3,
                    "endpoint" => "http://example3.com"
                ],
                [
                    "id" => 4,
                    "serviceId" => 1,
                    "type" => "type4",
                    "instances" => 4,
                    "endpoint" => "http://example4.com"
                ],
                [
                    "id" => 5,
                    "serviceId" => 1,
                    "type" => "type5",
                    "instances" => 5,
                    "endpoint" => "http://example5.com"
                ],
                [
                    "id" => 6,
                    "serviceId" => 2,
                    "type" => "type6",
                    "instances" => 6,
                    "endpoint" => "http://example6.com"
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
        return "serviceId";
    }
}
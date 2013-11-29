<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
//use CG\Order\Test\Api\Page\Service\SubscribedEventPage;

class ServicePage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/service";
    const EMBEDDED_RESOURCE = "service";
    const PRIMARY_ID = 1;
    const SECONDARY_ID = 2;

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
                [
                    "id" => 1,
                    "type" => "Type1",
                    "endpoint" => "endpoint1",
                ],
                [
                    "id" => 2,
                    "type" => "Type2",
                    "endpoint" => "endpoint2",
                ],
                [
                    "id" => 3,
                    "type" => "Type3",
                    "endpoint" => "endpoint3",
                ],
                [
                    "id" => 4,
                    "type" => "Type4",
                    "endpoint" => "endpoint4",
                ],
                [
                    "id" => 5,
                    "type" => "Type5",
                    "endpoint" => "endpoint5",
                ],
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            "type",
            "endpoint"
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            "type" => [],
            "endpoint" => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            "type",
            "endpoint"
        ];
    }

    public static function getEmbeddedResources()
    {
        return [
            "subscribedEvents[]" => SubscribedEventPage::class;
    }

//    public static function getChildPageClass()
//    {
//        return OrderItemPage::class;
//    }
}
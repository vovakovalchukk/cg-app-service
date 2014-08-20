<?php
namespace CG\Order\Test\Api\Page\Settings;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\RootPage;

class AliasPage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/settings/shipping/alias";
    const EMBEDDED_RESOURCE = "alias";
    const PRIMARY_ID = "1";
    const SECONDARY_ID = "2";

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
                "name" => "alias1",
                "organisationUnitId" => 1,
                "accountId" => 1,
                "shippingService" => "shippingService1",
                "methodIds" => [1, 2, 3],
            ],
            [
                "id" => 2,
                "name" => "alias2",
                "organisationUnitId" => 1,
                "accountId" => 2,
                "shippingService" => "shippingService2",
                "methodIds" => [1, 2]
            ],
            [
                "id" => 3,
                "name" => "alias3",
                "organisationUnitId" => 1,
                "accountId" => 1,
                "shippingService" => "shippingService3",
                "methodIds" => [4, 5, 6]
            ],
            [
                "id" => 4,
                "name" => "alias4",
                "organisationUnitId" => 1,
                "accountId" => 2,
                "shippingService" => "shippingService4",
                "methodIds" => [1, 7]
            ],
            [
                "id" => 5,
                "name" => "alias5",
                "organisationUnitId" => 1,
                "accountId" => 1,
                "shippingService" => "shippingService5",
                "methodIds" => [2, 3]
            ],
            [
                "id" => 6,
                "name" => "alias6",
                "organisationUnitId" => 1,
                "accountId" => 2,
                "shippingService" => "shippingService6",
                "methodIds" => [4, 5, 6]
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            "name",
            "organisationUnitId",
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            "id" => "foo",
            "name" => [],
            "organisationUnitId" => "ABC",
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            "name",
            "organisationUnitId",
            "methodIds"
        ];
    }

    public static function getFilterFields()
    {
        return [
            "id" => [],
            "organisationUnitId" => []
        ];
    }
}
 
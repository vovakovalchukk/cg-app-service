<?php
namespace CG\Order\Test\Api\Page\Settings\Clearbooks;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\RootPage;

class CustomerPage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/settings/clearbooks/customer";
    const EMBEDDED_RESOURCE = "customer";
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
                "clearbooksCustomerId" => 1,
                "organisationUnitId" => 1,
            ],
            [
                "id" => 2,
                "clearbooksCustomerId" => 2,
                "organisationUnitId" => 1,
            ],
            [
                "id" => 3,
                "clearbooksCustomerId" => 3,
                "organisationUnitId" => 1,
            ],
            [
                "id" => 4,
                "clearbooksCustomerId" => 4,
                "organisationUnitId" => 1,
            ],
            [
                "id" => 5,
                "clearbooksCustomerId" => 5,
                "organisationUnitId" => 1,
            ],
            [
                "id" => 6,
                "clearbooksCustomerId" => 6,
                "organisationUnitId" => 1,
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            "clearbooksCustomerId",
            "organisationUnitId",
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            "id" => "foo",
            'clearbooksCustomerId' => 'ABC',
            "organisationUnitId" => "ABC",
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            'clearbooksCustomerId',
            'organisationUnitId',
        ];
    }

    public static function getFilterFields()
    {
        return [
            "organisationUnitId" => []
        ];
    }
}

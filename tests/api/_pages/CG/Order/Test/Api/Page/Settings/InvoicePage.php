<?php
namespace CG\Order\Test\Api\Page\Settings;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\RootPage;

class InvoicePage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/settings/invoice";
    const PRIMARY_ID = 1;
    const SECONDARY_ID = 2;
    const EMBEDDED_RESOURCE = 'invoiceSettings';

    public static function getUrl()
    {
        return self::URL;
    }

    public static function getResourceNames()
    {
        return [
            static::EMBEDDED_RESOURCE
        ];
    }

    public static function notAllowedMethods()
    {
        return [
            static::POST   => static::POST,
            static::PUT    => static::PUT,
            static::DELETE => static::DELETE
        ];
    }

    public static function getTestCollection()
    {
        return [
            [
                "id" => 1,
                "default" => "2",
                "tradingCompanies" => [
                    "1" => "1"
                ]
            ], [
                "id" => 2,
                "default" => "3",
                "tradingCompanies" => [
                    "5" => "8",
                    "6" => "7",
                    "8" => "9"
                ]
            ]
        ];
    }

    public static function getTestCollectionKeys()
    {
        return [0, 1];
    }

    public static function getRequiredEntityFields()
    {
        return [
            "id",
            "default",
            "tradingCompanies"
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            "id" => 50,
            "default" => -1,
            "tradingCompanies" => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            "id",
            "default",
        ];
    }

    public static function getFilterFields()
    {
        return [
        ];
    }
}
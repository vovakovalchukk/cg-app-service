<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderEntityPage;

class UserChangePage extends OrderEntityPage
{
    use CollectionPageTrait;
    const URL = "/userChange";
    const EMBEDDED_RESOURCE = "userChange";

    public static function getParentEntityUrl($id)
    {
        return parent::getEntityUrl($id) . self::URL;
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
            [
                "changes" => ["shippingAddressCompanyName" => "Wilki Ltd"],
                "organisationUnitId" => 1
            ],
            [
                "changes" => ["totalDiscount" => 0.1],
                "organisationUnitId" => 2
            ],
            [
                "changes" => [
                    "billingAddressCompanyName" => "Wilki Ltd",
                    "billingAddressFullName" => "Matthew King"
                ],
                "organisationUnitId" => 3
            ],
            [
                "changes" => [
                    "totalDiscount" => 0.1,
                    "shippingAddressCompanyName" => "Wilki Ltd",
                    "shippingAddressFullName" => "Matthew King"
                ],
                "organisationUnitId" => 4
            ],
            [
                "changes" => [
                    "total" => 23.99,
                    "shippingPrice" => 13.99,
                    "totalDiscount" => 0.1,
                    "billingAddressCompanyName" => "Wilki Ltd",
                    "billingAddressFullName" => "Matthew King"
                ],
                "organisationUnitId" => 5
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return ["changes", "organisationUnitId"];
    }

    public static function getInvalidEntityData()
    {
        return ["changes" => "ABC", "organisationUnitId" => []];
    }

    public static function getInvalidEntityFields()
    {
        return ["changes", "organisationUnitId"];
    }
}
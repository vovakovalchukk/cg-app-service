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

    public static function getSecondaryTestEntity()
    {
        $entity = static::getTestCollection()[1];
        unset($entity['orderId']);
        return $entity;
    }

    public static function getTestCollection()
    {
        return [
            [
                "orderId" => "1411-10",
                "changes" => ["shippingAddressCompanyName" => "Wilki Ltd"],
                "organisationUnitId" => 1
            ],
            [
                "orderId" => "1412-20",
                "changes" => ["totalDiscount" => 0.1],
                "organisationUnitId" => 2
            ],
            [
                "orderId" => "1411-10",
                "changes" => [
                    "billingAddressCompanyName" => "Wilki Ltd",
                    "billingAddressFullName" => "Matthew King"
                ],
                "organisationUnitId" => 3
            ],
            [
                "orderId" => "1411-10",
                "changes" => [
                    "totalDiscount" => 0.1,
                    "shippingAddressCompanyName" => "Wilki Ltd",
                    "shippingAddressFullName" => "Matthew King"
                ],
                "organisationUnitId" => 4
            ],
            [
                "orderId" => "1411-10",
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
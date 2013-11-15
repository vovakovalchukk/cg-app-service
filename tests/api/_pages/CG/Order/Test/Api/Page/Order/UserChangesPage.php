<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderEntityPage;

class UserChangesPage extends OrderEntityPage
{
    use CollectionPageTrait;
    const URL = "/userChanges";
    const EMBEDDED_RESOURCE = "userChanges";

    public static function getUrl(){
        return self::URL;
    }

    static public function notAllowedMethods(){
        return [
                static::GET => static::GET,
                static::POST => static::POST,
                static::PUT => static::PUT,
                static::DELETE => static::DELETE
        ];
    }

    public static function getTestCollection(){

        return [
                [
                    "shippingAddress" => ["addressCompanyName" => "Wilki Ltd"]
                ],
                [
                    "totalDiscount" => 0.1
                ],
                [
                    "billingAddress" => ["addressCompanyName" => "Wilki Ltd",
                                        "addressFullName" => "Matthew King"
                                        ]
                ],
                [
                    "totalDiscount" => 0.1,
                    "billingAddress" => ["addressCompanyName" => "Wilki Ltd",
                                        "addressFullName" => "Matthew King"
                                        ]
                ],
                [
                    "total" => 23.99,
                    "shippingPrice" => 13.99,
                    "totalDiscount" => 0.1,
                    "billingAddress" => ["addressCompanyName" => "Wilki Ltd",
                                        "addressFullName" => "Matthew King"
                                        ]
                ]
               ];
    }

    public static function getRequiredEntityFields(){
        return [];
    }

    public static function getInvalidEntityData(){
        return [];
    }

    public static function getInvalidEntityFields(){
        return [];
    }
}
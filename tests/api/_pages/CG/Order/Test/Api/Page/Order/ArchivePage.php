<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderEntityPage;

class ArchivePage extends OrderEntityPage
{
    use CollectionPageTrait;
    const URL = "/archive";
    const EMBEDDED_RESOURCE = "archive";

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
                 "timestamp" => "2013-10-10 01:00:00"
                ],
                [
                 "timestamp" => "2013-10-10 02:00:00"
                ],
                [
                 "timestamp" => "2013-10-10 03:00:00"
                ],
                [
                 "timestamp" => "2013-10-10 04:00:00"
                ],
                [
                 "timestamp" => "2013-10-10 05:00:00"
                ],
               ];
    }

    public static function getRequiredEntityFields(){
        return ["timestamp"];
    }

    public static function getInvalidEntityData(){
        return [
                "timestamp" => []
               ];
    }

    public static function getInvalidEntityFields(){
        return ["timestamp"];
    }
}
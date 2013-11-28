<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderEntityPage;

class ArchivePage extends OrderEntityPage
{
    use CollectionPageTrait;
    const URL = "/archive";
    const EMBEDDED_RESOURCE = "archive";

    public static function getUrl()
    {
        return parent::getEntityUrl() . self::URL;
    }

    static public function notAllowedMethods()
    {
        return [
            static::POST => static::POST
        ];
    }

    public static function getTestCollection()
    {
        return [
                [
                "orderId" => "1411-10",
                "archived" => false
                ],
                [
                "orderId" => "1411-10",
                "archived" => false
                ],
                [
                "orderId" => "1411-10",
                "archived" => false
                ],
                [
                "orderId" => "1411-10",
                "archived" => false
                ],
                [
                "orderId" => "1411-10",
                "archived" => false
                ],
               ];
    }

    public static function getRequiredEntityFields()
    {
        return [];
    }

    public static function getInvalidEntityData()
    {
        return [];
    }

    public static function getInvalidEntityFields()
    {
        return [];
    }
}
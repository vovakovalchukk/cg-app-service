<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\EndpointsPageInterface;
use CG\Codeception\Cest\RestPage;
use CG\Order\Test\Api\Page\OrderPage;

class RootPage extends RestPage implements EndpointsPageInterface
{
    const EMBEDDED_RESOURCE = "";
    const PRIMARY_ID       = 1;
    const SECONDARY_ID     = 2;
    const NON_EXISTENT_ID  = 0;

    public static function getUrl()
    {
        return "/";
    }

    public static function getEndpoints()
    {
        return [
                "self"      => array("href" => static::getUrl()),
                "order"     => array("href" => OrderPage::getUrl())
        ];
    }

    public static function notAllowedMethods()
    {
        return [
                static::POST => static::POST,
                static::PUT => static::PUT,
                static::DELETE => static::DELETE
        ];
    }
}
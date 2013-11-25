<?php
namespace CG\Slim\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Slim\Test\Api\Page\RootPage;

class RestPage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/rest";

    public static function getUrl()
    {
        return self::URL;
    }

    public static function getStatusFilter()
    {
        return [
            ["status" => 200],
            ["status" => 400],
            ["status" => 404],
            ["status" => 409]
        ];
    }

    public static function getStatusFilterExpected()
    {
        $expected = [
            200 => [
                "status" => 200,
                "message" => ""
            ],
            400 => [
                "status" => 500,
                "message" => "unexpected exception"
            ],
            404 => [
                "status" => 404,
                "message" => "couldn't find entity"
            ],
            409 => [
                "status" => 500,
                "message" => "a conflict occurred"
            ]
        ];

        return $expected;
    }
}
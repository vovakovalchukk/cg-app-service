<?php
namespace CG\Slim\Test\Api\Page;

class RootPage
{
    const DEFAULT_PAGE_STRING = "Welcome to your Slim Skeleton Application";

    public static function getUrl()
    {
        return "/";
    }
}
<?php
abstract class RestPage
{
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    static public function allowedMethods()
    {
        return array_diff(array(
            static::GET,
            static::POST,
            static::PUT,
            static::DELETE
        ), static::notAllowedMethods());
    }

    abstract static public function notAllowedMethods();
}
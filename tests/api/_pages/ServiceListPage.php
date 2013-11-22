<?php
class ServiceListPage extends RestPage
{
    const URL = '/service';

    static public function getUrl()
    {
        return static::URL;
    }

    static public function notAllowedMethods()
    {
        return array(
            static::PUT,
            static::DELETE
        );
    }
}
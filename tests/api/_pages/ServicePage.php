<?php
class ServicePage extends RestPage
{
    const NEW_ID = 2;
    const VALID_ID = 1;
    const INVALID_ID = 0;

    static public function getUrl($id)
    {
        return ServiceListPage::getUrl() . '/' . urlencode($id);
    }

    static public function getNewServiceData()
    {
        return array(
            'type' => 'test',
            'endpoint' => 'http://example.com'
        );
    }

    static public function getValidServiceData()
    {
        return array(
            'id' => static::VALID_ID,
            'type' => 'test',
            'endpoint' => 'http://example.com'
        );
    }

    static public function getInvalidServiceData()
    {
        return array(
            'id' => static::INVALID_ID,
            'type' => 'test',
            'endpoint' => 'http://example.com'
        );
    }

    static public function notAllowedMethods()
    {
        return array(
            static::POST
        );
    }
}
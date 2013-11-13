<?php
class ServiceEventPage extends RestPage
{
    const NEW_TYPE = 'new';
    const VALID_TYPE = 'valid';
    const INVALID_TYPE = 'invalid';

    static public function getUrl($serviceId, $eventType)
    {
        return ServiceEventListPage::getUrl($serviceId) . '/' . $eventType;
    }

    static public function getNewEventData()
    {
        return array(
            'type' => static::NEW_TYPE,
            'instances' => 1,
            'endpoint' => 'http://example.com'
        );
    }

    static public function getValidEventData()
    {
        return array(
            'type' => static::VALID_TYPE,
            'instances' => 1,
            'endpoint' => 'http://example.com'
        );
    }

    static public function getInvalidEventData()
    {
        return array(
            'type' => static::INVALID_TYPE,
            'instances' => 1,
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
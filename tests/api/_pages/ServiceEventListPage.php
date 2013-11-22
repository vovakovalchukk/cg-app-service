<?php
class ServiceEventListPage extends RestPage
{
    const URL = '/subscribedEvents';

    static public function getUrl($serviceId)
    {
        return ServicePage::getUrl($serviceId) . static::URL;
    }

    static public function notAllowedMethods()
    {
        return array(
            static::PUT,
            static::DELETE
        );
    }
}
<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;

class ShippingMethodPage extends RootPage
{
    use CollectionPageTrait;

    const URL = '/shippingMethod';
    const EMBEDDED_RESOURCE = 'shippingMethod';
    const PRIMARY_ID = '1';
    const SECONDARY_ID = '2';

    public static function getUrl()
    {
        return self::URL;
    }

    public static function notAllowedMethods()
    {
        return [
            static::POST    => static::POST,
            static::PUT    => static::PUT,
            static::DELETE => static::DELETE
        ];
    }

    public static function getTestCollection()
    {
        return [
            [
                'id' => '1',
                'channel' => 'ebay',
                'method' => 'firstclass',
                'organisationUnitId' => '1'
            ],
            [
                'id' => '2',
                'channel' => 'amazon',
                'method' => 'firstclass',
                'organisationUnitId' => '1'
            ],
            [
                'id' => '3',
                'channel' => 'ebay',
                'method' => 'secondclass',
                'organisationUnitId' => '1'
            ],
            [
                'id' => '4',
                'channel' => 'amazon',
                'method' => 'secondclass',
                'organisationUnitId' => '1'
            ],
            [
                'id' => '5',
                'channel' => 'webstore',
                'method' => 'someclass',
                'organisationUnitId' => '1'
            ],
            [
                'id' => '6',
                'channel' => 'amazon',
                'method' => 'prime',
                'organisationUnitId' => '1'
            ],
            [
                'id' => '7',
                'channel' => 'ebay',
                'method' => 'nextyear',
                'organisationUnitId' => '1'
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            'channel',
            'method'
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            'channel' => [],
            'method' => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            'channel',
            'method'
        ];
    }

    public static function getFilterFields()
    {
        return [
            'id' => [],
            'channel' => [],
            'method' => [],
            'organisationUnitId' => []
        ];
    }
}
 
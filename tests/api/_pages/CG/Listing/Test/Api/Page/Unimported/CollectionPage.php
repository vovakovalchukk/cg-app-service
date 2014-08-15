<?php
namespace CG\Listing\Test\Api\Page\Unimported;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\RootPage;

class CollectionPage extends RootPage
{
    use CollectionPageTrait;

    const URL = '/unimportedListing';
    const EMBEDDED_RESOURCE = 'unimportedListing';
    const PRIMARY_ID = '1';
    const SECONDARY_ID = '2';

    public static function getUrl()
    {
        return self::URL;
    }

    public static function notAllowedMethods()
    {
        return [
            static::PUT    => static::PUT,
            static::DELETE => static::DELETE
        ];
    }

    public static function getTestCollection()
    {
        return [
            [
                'id' => 1,
                'organisationUnitId' => 1,
                'accountId' => 2,
                'externalId' =>'anExternalID',
                'sku' => 'anSKU',
                'title' => 'aTitle',
                'url' => 'www.channelgrabber.com',
                'imageId' => 1,
                'createdDate' => '2014-08-14 14:00:00',
                'status' => 'open',
                'variationCount' => 1,
            ],
            [
                'id' => 2,
                'organisationUnitId' => 1,
                'accountId' => 2,
                'externalId' =>'anExternalID2',
                'sku' => 'anSKU2',
                'title' => 'aTitle2',
                'url' => 'www.reddit.com',
                'imageId' => 2,
                'createdDate' => '2014-08-15 14:00:00',
                'status' => 'lost',
                'variationCount' => 1
            ],
            [
                'id' => 3,
                'organisationUnitId' => 1,
                'accountId' => 2,
                'externalId' =>'anExternalID3',
                'sku' => 'anSKU3',
                'title' => 'aTitle3',
                'url' => 'www.bbc.co.uk',
                'imageId' => 3,
                'createdDate' => '2014-08-16 14:00:00',
                'status' => 'closed',
                'variationCount' => 1
            ],
            [
                'id' => 4,
                'organisationUnitId' => 2,
                'accountId' => 2,
                'externalId' =>'anExternalID4',
                'sku' => 'anSKU4',
                'title' => 'aTitle4',
                'url' => 'www.sky.com',
                'imageId' => 1,
                'createdDate' => '2014-08-14 14:00:00',
                'status' => 'lost',
                'variationCount' => 3
            ],
            [
                'id' => 5,
                'organisationUnitId' => 2,
                'accountId' => 3,
                'externalId' =>'anExternalID5',
                'sku' => 'anSKU5',
                'title' => 'aTitle5',
                'url' => 'www.gamerscripts.com',
                'imageId' => 3,
                'createdDate' => '2015-08-14 14:00:00',
                'status' => 'theFuture',
                'variationCount' => 10
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            'organisationUnitId',
            'accountId',
            'externalId',
            'sku',
            'title',
            'url',
            'imageId',
            'createdDate',
            'status',
            'variationCount'
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            'organisationUnitId' => [],
            'accountId' => [],
            'externalId' => [],
            'sku' => [],
            'title' => [],
            'url' => [],
            'imageId' => "aString",
            'createdDate' => "9999-99-99 99:99:99",
            'status' => [],
            'variationCount' => "aString"
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            'organisationUnitId',
            'accountId',
            'externalId',
            'sku',
            'title',
            'url',
            'imageId',
            'createdDate',
            'status',
            'variationCount'
        ];
    }

    public static function getFilterFields()
    {
        return [
            'id' => [],
            'organisationUnitId' => [],
            'accountId' => [],
            'externalId' => [],
            'sku' => [],
            'title' => [],
            'url' => [],
            'imageId' => [],
            'createdDate' => [],
            'status' => [],
            'variationCount' => []
        ];
    }
}
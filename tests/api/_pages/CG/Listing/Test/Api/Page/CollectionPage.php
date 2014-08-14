<?php
namespace CG\Listing\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Listing\Status;
use CG\Order\Test\Api\Page\RootPage;

class CollectionPage extends RootPage
{
    use CollectionPageTrait;

    const URL = '/listing';
    const EMBEDDED_RESOURCE = 'listing';
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
                'productId' => 1,
                'externalId' => '1',
                'channel' => 'ebay',
                'status' => Status::ACTIVE,
            ],
            [
                'id' => 2,
                'organisationUnitId' => 1,
                'productId' => 2,
                'externalId' => '2',
                'channel' => 'ebay',
                'status' => Status::ACTIVE,
            ],
            [
                'id' => 3,
                'organisationUnitId' => 1,
                'productId' => 3,
                'externalId' => '3',
                'channel' => 'ebay',
                'status' => Status::INACTIVE,
            ],
            [
                'id' => 4,
                'organisationUnitId' => 1,
                'productId' => 1,
                'externalId' => 'A',
                'channel' => 'amazon',
                'status' => Status::ACTIVE,
            ],
            [
                'id' => 5,
                'organisationUnitId' => 1,
                'productId' => 2,
                'externalId' => 'B',
                'channel' => 'amazon',
                'status' => Status::INACTIVE,
            ],
            [
                'id' => 6,
                'organisationUnitId' => 1,
                'productId' => 3,
                'externalId' => 'C',
                'channel' => 'amazon',
                'status' => Status::ACTIVE,
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            'organisationUnitId',
            'productId',
            'externalId',
            'channel',
            'status'
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            'organisationUnitId' => [],
            'productId' => [],
            'externalId' => [],
            'channel' => [],
            'status' => [],
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            'organisationUnitId',
            'productId',
            'externalId',
            'channel',
            'status'
        ];
    }

    public static function getFilterFields()
    {
        return [
            'id' => [],
            'organisationUnitId' => [],
            'productId' => [],
            'externalId' => [],
            'channel' => [],
            'status' => [],
        ];
    }
}

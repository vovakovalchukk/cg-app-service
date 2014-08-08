<?php
namespace CG\Stock\Test\Api\Page\Location;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\RootPage;

class CollectionPage extends RootPage
{
    use CollectionPageTrait;

    const URL = '/stockLocation';
    const EMBEDDED_RESOURCE = 'location';
    const PRIMARY_ID = '1-1';
    const SECONDARY_ID = '2-1';

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
                'id' => '1-1',
                'stockId' => '1',
                'locationId' => '1',
                'onHand' => 0,
                'allocated' => 0
            ],
            [
                'id' => '2-1',
                'stockId' => '2',
                'locationId' => '1',
                'onHand' => 0,
                'allocated' => 0
            ],
            [
                'id' => '3-1',
                'stockId' => '3',
                'locationId' => '1',
                'onHand' => 0,
                'allocated' => 0
            ],
            [
                'id' => '4-1',
                'stockId' => '4',
                'locationId' => '1',
                'onHand' => 0,
                'allocated' => 0
            ],
            [
                'id' => '5-1',
                'stockId' => '5',
                'locationId' => '1',
                'onHand' => 0,
                'allocated' => 0
            ],
            [
                'id' => '6-1',
                'stockId' => '6',
                'locationId' => '1',
                'onHand' => 0,
                'allocated' => 0
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            'stockId',
            'locationId',
            'onHand',
            'allocated'
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            'stockId' => [],
            'locationId' => [],
            'onHand' => [],
            'allocated' => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            'stockId',
            'locationId',
            'onHand',
            'allocated'
        ];
    }

    public static function getFilterFields()
    {
        return [
            'stockId' => [],
            'locationId' => [],
        ];
    }

    /**
     * Overridden from trait as that tries to change the stockId which forms part of the ID so we can't do that
     */
    public static function getPrimaryUpdatedTestEntity()
    {
        $primary = static::getPrimaryTestEntity();
        $primary['onHand'] += 5;
        return $primary;
    }
}

<?php
namespace CG\Location\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;

class LocationPage extends RootPage
{
    use CollectionPageTrait;

    const URL = '/location';
    const EMBEDDED_RESOURCE = 'location';
    const PRIMARY_ID = '1';
    const SECONDARY_ID = '2';

    public static function getUrl()
    {
        return self::URL;
    }

    public static function notAllowedMethods()
    {
        return [
            static::PUT     => static::PUT,
            static::DELETE  => static::DELETE
        ];
    }

    public static function getTestCollection()
    {
        return [
            [
                'id' => '1',
                'organisationUnitId' => '2'
            ],
            [
                'id' => '2',
                'organisationUnitId' => '2'
            ],
            [
                'id' => '3',
                'organisationUnitId' => '3'
            ],
            [
                'id' => '4',
                'organisationUnitId' => '2'
            ],
            [
                'id' => '5',
                'organisationUnitId' => '3'
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            'organisationUnitId'
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            'organisationUnitId' => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            'organisationUnitId'
        ];
    }

    public static function getFilterFields()
    {
        return [
            'id' => [],
            'organisationUnitId' => []
        ];
    }
}
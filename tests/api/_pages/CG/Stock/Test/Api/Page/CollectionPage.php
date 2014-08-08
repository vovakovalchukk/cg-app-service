<?php
namespace CG\Stock\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\RootPage;

class CollectionPage extends RootPage
{
    use CollectionPageTrait;

    const URL = '/stock';
    const EMBEDDED_RESOURCE = 'stock';
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
                'id' => '1',
                'organisationUnitId' => '1',
                'sku' => 'sku1',
            ],
            [
                'id' => '2',
                'organisationUnitId' => '1',
                'sku' => 'sku2',
            ],
            [
                'id' => '3',
                'organisationUnitId' => '1',
                'sku' => 'sku3',
            ],
            [
                'id' => '4',
                'organisationUnitId' => '1',
                'sku' => 'sku4',
            ],
            [
                'id' => '5',
                'organisationUnitId' => '1',
                'sku' => 'sku5',
            ],
            [
                'id' => '6',
                'organisationUnitId' => '1',
                'sku' => 'sku6',
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            'organisationUnitId',
            'sku'
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            'organisationUnitId' => [],
            'sku' => 123,
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            'organisationUnitId',
            'sku',
        ];
    }

    public static function getFilterFields()
    {
        return [
            'id' => [],
            'organisationUnitId' => [],
            'sku' => []
        ];
    }
}

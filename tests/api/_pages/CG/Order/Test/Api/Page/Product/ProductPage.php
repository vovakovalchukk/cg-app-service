<?php
namespace CG\Order\Test\Api\Page\Product;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\RootPage;

class ProductPage extends RootPage
{
    use CollectionPageTrait;

    const URL = '/product';
    const EMBEDDED_RESOURCE = 'product';
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
                'name' => 'product1'
            ],
            [
                'id' => '2',
                'organisationUnitId' => '1',
                'sku' => 'sku2',
                'name' => 'product2'
            ],
            [
                'id' => '3',
                'organisationUnitId' => '1',
                'sku' => 'sku3',
                'name' => 'product3'
            ],
            [
                'id' => '4',
                'organisationUnitId' => '1',
                'sku' => 'sku4',
                'name' => 'product4'
            ],
            [
                'id' => '5',
                'organisationUnitId' => '1',
                'sku' => 'sku5',
                'name' => 'product5'
            ],
            [
                'id' => '6',
                'organisationUnitId' => '1',
                'sku' => 'sku6',
                'name' => 'product6'
            ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return [
            'organisationUnitId',
            'name'
        ];
    }

    public static function getInvalidEntityData()
    {
        return [
            'organisationUnitId' => [],
            'sku' => 123,
            'name' => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return [
            'organisationUnitId',
            'sku',
            'name'
        ];
    }

    public static function getFilterFields()
    {
        return [
            'organisationUnitId' => []
        ];
    }
}

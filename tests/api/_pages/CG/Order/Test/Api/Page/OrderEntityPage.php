<?php
namespace CG\Order\Test\Api\Page;

use CG\Order\Test\Api\Page\OrderPage;
use CG\Order\Test\Api\Page\Order\NotePage;
use CG\Order\Test\Api\Page\Order\TrackingPage;
use CG\Order\Test\Api\Page\Order\AlertPage;
use CG\Order\Test\Api\Page\Order\UserChangePage;
use CG\Order\Test\Api\Page\OrderItemPage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class OrderEntityPage extends OrderPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return OrderPage::class;
    }

    public static function getDeletedTagUrl()
    {
        return '/orderTag/1411-10-tag2';
    }

    public static function getUpdatedTagUrl()
    {
        return '/orderTag/1411-10-tag6';
    }

    public static function notAllowedMethods()
    {
        return [
                static::POST => static::POST
        ];
    }

    public static function getEmbeddedResources()
    {
        return [
            "item[]" => OrderItemPage::class,
            "note[]" => NotePage::class,
            "tracking[]" => TrackingPage::class,
            "alert[]" => AlertPage::class,
            "userChange" => UserChangePage::class
        ];
    }

    public static function getPrimaryTestEntity()
    {
        $entity = static::getTestCollection()[0];
        unset($entity["archived"]);
        return $entity;
    }

    public static function getSecondaryTestEntity()
    {
        $entity = static::getTestCollection()[1];
        unset($entity["archived"]);
        return $entity;
    }

    public static function getNewEntityData(){
        $newEntityData = self::getTestCollection()[0];
        unset($newEntityData['id']);
        unset($newEntityData['archived']);
        return $newEntityData;
    }

    public static function getWrongParentCheckExcludedResources()
    {
        return [
            "item[]" => OrderItemPage::class,
            "userChange" => UserChangePage::class
        ];
    }
}
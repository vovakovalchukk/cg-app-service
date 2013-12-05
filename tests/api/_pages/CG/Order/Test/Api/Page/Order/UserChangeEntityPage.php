<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Order\Test\Api\Page\Order\UserChangePage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class UserChangeEntityPage extends UserChangePage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return UserChangePage::class;
    }

    public static function getEntityUrl($id = parent::PRIMARY_ID)
    {
        return parent::getParentEntityUrl($id);
    }

    public static function getUrl($id = parent::PRIMARY_ID)
    {
        return parent::getParentEntityUrl($id);
    }

    public static function getSecondaryEntityUrl()
    {
        return static::getUrl(parent::SECONDARY_ID);
    }

    public static function notAllowedMethods()
    {
        return [
            static::POST => static::POST
        ];
    }
}
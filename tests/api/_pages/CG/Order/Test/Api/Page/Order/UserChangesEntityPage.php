<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Order\Test\Api\Page\Order\UserChangesPage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class UserChangesEntityPage extends UserChangesPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return UserChangesPage::class;
    }

    public static function notAllowedMethods()
    {
        return [
                static::POST => static::POST,
                static::PUT => static::PUT
        ];
    }
}
<?php
namespace CG\Order\Test\Api\Page;

use CG\Order\Test\Api\Page\ServicePage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class ServiceEntityPage extends ServicePage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return ServicePage::class;
    }

    public static function notAllowedMethods()
    {
        return [
            static::POST => static::POST
        ];
    }
}
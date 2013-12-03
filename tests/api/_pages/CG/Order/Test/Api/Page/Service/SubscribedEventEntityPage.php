<?php
namespace CG\Order\Test\Api\Page\Service;

use CG\Order\Test\Api\Page\Service\SubscribedEventPage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class SubscribedEventEntityPage extends SubscribedEventPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return SubscribedEventPage::class;
    }

    public static function notAllowedMethods()
    {
        return [
                static::POST => static::POST
        ];
    }
}
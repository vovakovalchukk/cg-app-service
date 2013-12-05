<?php
namespace CG\Order\Test\Api\Page\OrderItem;

use CG\Order\Test\Api\Page\OrderItem\FeePage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class FeeEntityPage extends FeePage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return FeePage::class;
    }

    public static function notAllowedMethods()
    {
        return [
                static::POST => static::POST
        ];
    }
}
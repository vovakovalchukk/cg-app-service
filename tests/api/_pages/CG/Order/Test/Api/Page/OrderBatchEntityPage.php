<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class OrderBatchEntityPage extends OrderBatchPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return OrderBatchPage::class;
    }

    public static function notAllowedMethods()
    {
        return [
            static::POST => static::POST
        ];
    }
}
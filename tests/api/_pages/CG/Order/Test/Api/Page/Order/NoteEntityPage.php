<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Order\Test\Api\Page\Order\NotePage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class NoteEntityPage extends NotePage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return NotePage::class;
    }

    public static function notAllowedMethods()
    {
        return [
                static::POST => static::POST
        ];
    }
}
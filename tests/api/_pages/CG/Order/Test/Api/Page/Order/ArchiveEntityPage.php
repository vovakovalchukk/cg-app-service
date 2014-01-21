<?php
namespace CG\Order\Test\Api\Page\Order;

use CG\Order\Test\Api\Page\Order\ArchivePage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class ArchiveEntityPage extends ArchivePage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return ArchivePage::class;
    }

    public static function getEntityUrl($id = self::PRIMARY_ID)
    {
        return parent::getUrl();
    }

    public static function getUrl()
    {
        return parent::getUrl();
    }

    public static function notAllowedMethods()
    {
        return [
                static::POST => static::POST,
                static::DELETE => static::DELETE
        ];
    }
}
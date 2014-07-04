<?php
namespace CG\Order\Test\Api\Page\Settings;

use CG\Order\Test\Api\Page\ShippingMethodPage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class AliasEntityPage extends AliasPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return AliasPage::class;
    }

    public static function notAllowedMethods()
    {
        return [
            static::POST => static::POST
        ];
    }

    public static function geEmbeddedResources()
    {
        return [
            "method[]" => ShippingMethodPage::class
        ];
    }
}

 
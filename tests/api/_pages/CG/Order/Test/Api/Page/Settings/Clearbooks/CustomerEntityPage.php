<?php
namespace CG\Order\Test\Api\Page\Settings\Clearbooks;

use CG\Order\Test\Api\Page\ShippingMethodPage;
use CG\Codeception\Cest\Rest\EntityPageTrait;
use CG\Codeception\Cest\Rest\EntityPageInterface;

class CustomerEntityPage extends CustomerPage implements EntityPageInterface
{
    use EntityPageTrait;

    public static function getCollectionPage()
    {
        return CustomerPage::class;
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
        ];
    }
}


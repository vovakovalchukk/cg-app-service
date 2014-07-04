<?php
namespace CG\Order\Test\Api\Cest\Settings;

use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Order\Test\Api\Page\Settings\AliasPage;

class AlertCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return AliasPage::class;
    }
}
 
<?php
namespace CG\Order\Test\Api\Cest\Settings\Clearbooks;

use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Order\Test\Api\Page\Settings\Clearbooks\CustomerPage;

class CustomerCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return CustomerPage::class;
    }
}

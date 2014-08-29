<?php
namespace CG\Order\Test\Api\Cest\Settings\Clearbooks;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Codeception\Cest\Rest\EntityETagTrait;
use CG\Order\Test\Api\Page\Settings\Clearbooks\CustomerEntityPage;

class CustomerEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return CustomerEntityPage::class;
    }
}

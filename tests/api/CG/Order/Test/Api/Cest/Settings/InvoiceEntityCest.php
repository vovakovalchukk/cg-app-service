<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\Settings\InvoiceEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class InvoiceEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return InvoiceEntityPage::class;
    }
}
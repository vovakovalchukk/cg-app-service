<?php
namespace CG\Order\Test\Api\Cest\Settings;

use CG\Order\Test\Api\Page\Settings\InvoicePage;
use CG\Codeception\Cest\Rest\CollectionTrait;

class InvoiceCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return InvoicePage::class;
    }
}
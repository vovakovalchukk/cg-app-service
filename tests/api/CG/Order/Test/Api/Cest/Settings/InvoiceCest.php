<?php
namespace CG\Order\Test\Api\Cest\Settings;

use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Order\Test\Api\Page\Settings\InvoicePage;

class InvoiceCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return InvoicePage::class;
    }
}
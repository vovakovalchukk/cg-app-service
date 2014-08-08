<?php
namespace CG\Stock\Test\Api\Cest;

use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Stock\Test\Api\Page\CollectionPage;

class CollectionCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return CollectionPage::class;
    }
}
 
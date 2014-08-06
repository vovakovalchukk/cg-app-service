<?php
namespace CG\Stock\Test\Api\Cest\Location;

use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Stock\Test\Api\Page\Location\CollectionPage;

class CollectionCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return CollectionPage::class;
    }
}
 
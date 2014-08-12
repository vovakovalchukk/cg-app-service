<?php
namespace CG\Listing\Test\Api\Cest;

use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Listing\Test\Api\Page\CollectionPage;

class CollectionCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return CollectionPage::class;
    }
}
 
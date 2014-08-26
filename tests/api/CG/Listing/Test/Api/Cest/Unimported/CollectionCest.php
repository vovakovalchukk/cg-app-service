<?php
namespace CG\Listing\Test\Api\Cest\Unimported;

use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Listing\Test\Api\Page\Unimported\CollectionPage;

class CollectionCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return CollectionPage::class;
    }
}
 
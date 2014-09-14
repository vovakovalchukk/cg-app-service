<?php
namespace CG\Location\Test\Api\Cest;

use CG\Location\Test\Api\Page\LocationPage;
use CG\Codeception\Cest\Rest\CollectionTrait;

class LocationCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return LocationPage::class;
    }
}
<?php
namespace CG\Location\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Codeception\Cest\Rest\EntityETagTrait;
use CG\Location\Test\Api\Page\LocationEntityPage;

class LocationEntityCest
{
    use EntityTrait;
    use EntityETagTrait;

    protected function getPageClass()
    {
        return LocationEntityPage::class;
    }
}
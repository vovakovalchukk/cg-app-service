<?php
namespace CG\Stock\Test\Api\Cest\Location;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Codeception\Cest\Rest\EntityETagTrait;
use CG\Stock\Test\Api\Page\Location\EntityPage;

class EntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return EntityPage::class;
    }
}
 
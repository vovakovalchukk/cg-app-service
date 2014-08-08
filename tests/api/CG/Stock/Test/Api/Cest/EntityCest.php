<?php
namespace CG\Stock\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Codeception\Cest\Rest\EntityETagTrait;
use CG\Stock\Test\Api\Page\EntityPage;

class EntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return EntityPage::class;
    }
}
 
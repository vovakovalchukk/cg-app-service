<?php
namespace CG\Listing\Test\Api\Cest\Unimported;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Codeception\Cest\Rest\EntityETagTrait;
use CG\Listing\Test\Api\Page\Unimported\EntityPage;

class EntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return EntityPage::class;
    }
}
 
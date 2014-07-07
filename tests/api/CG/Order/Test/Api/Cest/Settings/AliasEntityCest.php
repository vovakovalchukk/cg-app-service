<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Codeception\Cest\Rest\EntityETagTrait;
use CG\Order\Test\Api\Page\Settings\AliasEntityPage;

class AliasEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return AliasEntityPage::class;
    }
}
 
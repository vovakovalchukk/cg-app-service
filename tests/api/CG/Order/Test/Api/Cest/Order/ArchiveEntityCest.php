<?php
namespace CG\Order\Test\Api\Cest\Order;

use ApiGuy;
use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\Order\ArchiveEntityPage;

class ArchiveEntityCest
{
    use EntityTrait;

    protected function getPageClass()
    {
        return ArchiveEntityPage::class;
    }

    public function viewNonExistentEntity(ApiGuy $I)
    {
        $I->amGoingTo('skip viewing non existent entity as an entity always exists');
        return;
    }
}
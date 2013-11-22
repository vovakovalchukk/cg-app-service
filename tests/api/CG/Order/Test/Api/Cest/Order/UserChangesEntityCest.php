<?php
namespace CG\Order\Test\Api\Cest\Order;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\Order\UserChangesEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class UserChangesEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return UserChangesEntityPage::class;
    }
}
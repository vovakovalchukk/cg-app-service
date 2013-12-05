<?php
namespace CG\Order\Test\Api\Cest\Order;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\Order\UserChangeEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class UserChangeEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return UserChangeEntityPage::class;
    }
}
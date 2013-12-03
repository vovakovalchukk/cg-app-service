<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\ServiceEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class ServiceEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return ServiceEntityPage::class;
    }
}
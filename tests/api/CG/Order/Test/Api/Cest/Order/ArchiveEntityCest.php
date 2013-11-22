<?php
namespace CG\Order\Test\Api\Cest\Order;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\Order\ArchiveEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class ArchiveEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return ArchiveEntityPage::class;
    }
}
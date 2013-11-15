<?php
namespace CG\Order\Test\Api\Cest\Order;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\Order\NoteEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class NoteEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return NoteEntityPage::class;
    }
}
<?php
namespace CG\Order\Test\Api\Cest;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Order\Test\Api\Page\TemplateEntityPage;
use CG\Codeception\Cest\Rest\EntityETagTrait;

class TemplateEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return TemplateEntityPage::class;
    }
}
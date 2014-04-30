<?php
namespace CG\Order\Test\Api\Cest;

use CG\Order\Test\Api\Page\TemplatePage;
use CG\Codeception\Cest\Rest\CollectionTrait;

class TemplateCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return TemplatePage::class;
    }
}
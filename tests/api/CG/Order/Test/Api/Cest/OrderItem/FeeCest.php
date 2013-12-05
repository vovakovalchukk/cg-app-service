<?php
namespace CG\Order\Test\Api\Cest\OrderItem;

use CG\Order\Test\Api\Page\OrderItem\FeePage;
use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;

class FeeCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return FeePage::class;
    }
}
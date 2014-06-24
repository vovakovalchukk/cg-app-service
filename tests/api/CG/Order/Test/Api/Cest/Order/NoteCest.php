<?php
namespace CG\Order\Test\Api\Cest\Order;

use CG\Order\Test\Api\Page\Order\NotePage;
use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;

class NoteCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return NotePage::class;
    }
}
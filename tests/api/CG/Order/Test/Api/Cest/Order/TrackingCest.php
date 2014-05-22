<?php
namespace CG\Order\Test\Api\Cest\Order;

use CG\Order\Test\Api\Page\Order\TrackingPage;
use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;

class TrackingCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return TrackingPage::class;
    }
    
    public function viewCollectionAll(ApiGuy $I)
    {
    	$I->amGoingTo('skip viewing collection as getTestCollection() should be filtered by order_id first.');
    }
}
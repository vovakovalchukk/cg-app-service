<?php
namespace CG\Order\Test\Api\Cest\Order;

use CG\Order\Test\Api\Page\Order\AlertPage;
use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;

class AlertCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return AlertPage::class;
    }
    
    public function viewCollectionAll(ApiGuy $I)
    {	
    	$I->amGoingTo('skip viewing collection as getTestCollection() should be filtered by order_id first.');
    }
    
 	public function checkSortCollection(ApiGuy $I)
    {      
    	$I->amGoingTo('skip viewing collection with sort as sorting alerts has not been implemented yet');      
    }
}
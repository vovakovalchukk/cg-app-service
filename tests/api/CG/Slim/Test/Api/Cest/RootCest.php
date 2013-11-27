<?php
namespace CG\Order\Test\Api\Cest;

use CG\Slim\Test\Api\Page\RootPage;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;

class RootCest
{
    protected function getPageClass()
    {
        return RootPage::class;
    }

    /**
     * @group get
     * @group custom
     * @group slim
     **/
    public function checkWelcomePage(ApiGuy $I)
    {
        $page = static::getPageClass();

        $I->wantTo('see the Root page returns correct status code and the welcome message');
        $I->prepareRequest();
        $I->sendGET($page::getUrl());
        $I->seeResponseCodeIs(HttpStatus::OK);
        $I->seeResponseContains(RootPage::DEFAULT_PAGE_STRING);
    }
}
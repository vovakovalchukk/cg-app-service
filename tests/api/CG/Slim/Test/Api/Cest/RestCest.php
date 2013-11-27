<?php
namespace CG\Slim\Test\Api\Cest;

use CG\Slim\Test\Api\Page\RestPage;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;
use Codeception\Module\ApiHelper;

class RestCest
{
    protected function getPageClass()
    {
        return RestPage::class;
    }

    /**
     * @group filter
     * @group get
     * @group custom
     * @group slim
     **/
    public function checkStatusFilter(ApiGuy $I)
    {
        $page = static::getPageClass();

        $filters = $page::getStatusFilter();

        $expectedResult = $page::getStatusFilterExpected();

        foreach ($filters as $filter) {
            foreach ($filter as $filterField => $filterValue) {
                $url = ApiHelper::appendFilters($page::getUrl(), [$filterField => $filterValue]);

                $I->wantTo('see the Rest page returns correct results when filtered by status');
                $I->prepareRequest();
                $I->sendGET($url);
                $I->seeResponseCodeIs($expectedResult[$filterValue][$filterField]);

                if ($filterValue != HttpStatus::OK) {
                    $expected = ["logref" => null, "message" => $expectedResult[$filterValue]["message"]];
                    $I->seeResponseContains(json_encode($expected));
                }
            }
        }
    }
}
<?php
namespace CG\Slim\Test\Api\Cest;

use CG\Slim\Test\Api\Page\RestPage;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;

class RestCest
{
    protected function getPageClass()
    {
        return RestPage::class;
    }

    protected function appendFilters($url, $filters = array())
    {
        if (empty($filters)) {
            return $url;
        }
        http_build_url(
            $url,
            array('query' => urldecode(http_build_query($filters))),
            HTTP_URL_JOIN_QUERY,
            $parts
        );
        return preg_replace('/\[\d+\]/','[]', urldecode($parts['path'] . "?" . $parts['query']));
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
                $url = $this->appendFilters($page::getUrl(), [$filterField => $filterValue]);

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
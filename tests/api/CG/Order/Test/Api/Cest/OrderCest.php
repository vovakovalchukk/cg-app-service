<?php
namespace CG\Order\Test\Api\Cest;

use CG\Order\Test\Api\Page\OrderPage;
use CG\Order\Test\Api\Page\OrderItemPage;
use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;

class OrderCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return OrderPage::class;
    }

    /**
     * @group filter
     * @group get
     * @group custom
     **/
    public function checkCountryFilter(ApiGuy $I)
    {
        $page = static::getPageClass();

        $filters = $page::getCountryFilter();
        $url = $this->appendFilters($page::getUrl(), $filters);
        $expectedResult = $page::getCountryFilterExpected();

        $I->wantTo('see the Order Collection returns correct results when filtered by country');
        $I->prepareRequest();
        $I->sendGET($url);
        $I->seeResponseCodeIs(HttpStatus::OK);

        $I->seeJsonFieldContainsArrayValues("_embedded.".$page::EMBEDDED_RESOURCE, $expectedResult);
    }

    /**
     * @group filter
     * @group get
     * @group custom
     **/
    public function checkCountryExcludeFilter(ApiGuy $I)
    {
        $page = static::getPageClass();

        $filters = $page::getCountryExcludeFilter();
        $url = $this->appendFilters($page::getUrl(), $filters);
        $expectedResult = $page::getCountryExcludeFilterExpected();

        $I->wantTo('see the Order Collection returns correct results when filtered by country');
        $I->prepareRequest();
        $I->sendGET($url);
        $I->seeResponseCodeIs(HttpStatus::OK);

        $I->seeJsonFieldContainsArrayValues("_embedded.".$page::EMBEDDED_RESOURCE, $expectedResult);
    }

    /**
     * @group filter
     * @group get
     * @group custom
     **/
    public function checkMultiLineFilter(ApiGuy $I)
    {
        $page = static::getPageClass();

        $filters = $page::getMultiLineFilter();

        foreach ($filters as $key => $filterValues) {
            foreach ($filterValues as $filter) {
                $url = $this->appendFilters($page::getUrl(), [$key => $filter]);

                $expectedResult = $page::getMultiLineFilterExpected($filter);

                $I->wantTo('see the Order Collection returns correct results when filtered by multiline orders');
                $I->prepareRequest();
                $I->sendGET($url);
                $I->seeResponseCodeIs(HttpStatus::OK);

                $I->seeJsonFieldContainsArrayValues("_embedded.".$page::EMBEDDED_RESOURCE, $expectedResult);
                $I->seeEmbeddedTypeIsOfSize($page::EMBEDDED_RESOURCE, count($expectedResult));
            }
        }
    }

    /**
     * @group filter
     * @group get
     * @group custom
     **/
    public function checkMultiSameItemFilter(ApiGuy $I)
    {
        $page = static::getPageClass();

        $filters = $page::getMultiSameItemFilter();

        foreach ($filters as $key => $filterValues) {
            foreach ($filterValues as $filter) {
                $url = $this->appendFilters($page::getUrl(), [$key => $filter]);

                $expectedResult = $page::getMultiSameItemFilterExpected($filter);

                $I->wantTo('see the Order Collection returns correct results when filtered by multi same item orders');
                $I->prepareRequest();
                $I->sendGET($url);
                $I->seeResponseCodeIs(HttpStatus::OK);

                $I->seeJsonFieldContainsArrayValues("_embedded.".$page::EMBEDDED_RESOURCE, $expectedResult);
                $I->seeEmbeddedTypeIsOfSize($page::EMBEDDED_RESOURCE, count($expectedResult));
            }
        }
    }
}
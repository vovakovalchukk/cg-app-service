<?php
namespace CG\Order\Test\Api\Cest;

use CG\Order\Test\Api\Page\OrderPage;
use CG\Order\Test\Api\Page\OrderItemPage;
use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Http\StatusCode as HttpStatus;
use ApiGuy;
use CG\Order\Test\Api\Page\FilterPage;

class OrderCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return OrderPage::class;
    }

    /**
     * @group get
     * @group custom
     * @group orderFilter
     */
    public function postingFilterAndFilteringOrdersByFilterIdReturnsExpectedResults(ApiGuy $I)
    {
        $I->wantTo(
            'check posting to /orderFilter and then filtering by orderFilter returns correct orders'
        );
        $page = $this->getPageClass();
        $filterData = $page::getFilterData();
        $I->prepareRequestForContent();
        $I->sendPOST($page::FILTER_URL, $filterData);
        $I->seeResponseCodeIs(HttpStatus::CREATED);
        $filterId = $I->grabDataFromJsonResponse("id")->__value();

        $url = $this->appendFilters($page::getUrl(), ["orderFilter" => $filterId]);
        $I->prepareRequest();
        $I->sendGET($url);
        $I->seeResponseCodeIs(HttpStatus::OK);
        $I->seeEmbeddedTypeIsOfSize($page::EMBEDDED_RESOURCE, 1);
    }

    /**
     * @group get
     * @group custom
     * @group orderFilter
     */
    public function requestingMutuallyExclusiveFiltersReturns400(ApiGuy $I)
    {
        $page = $this->getPageClass();
        $filters = $page::getMutuallyExclusiveFilters();
        $url = $this->appendFilters($page::getUrl(), $filters);

        $I->wantTo('check sending mutually exclusive filters throw a 400 Bad Request');
        $I->prepareRequest();
        $I->sendGET($url);
        $I->seeResponseCodeIs(HttpStatus::BAD_REQUEST);
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

    /**
     * @group filter
     * @group get
     * @group custom
     */
    public function checkIncludeArchivedFilter(ApiGuy $I)
    {
        $page = static::getPageClass();

        $filterFieldArray = $page::getArchiveFilter();

        $I->wantTo("check archive filter works for this collection");

        $filterFieldValues = [true, false];

        foreach ($filterFieldArray as $filterParameter => $filterField) {
            foreach ($filterFieldValues as $filterFieldValue) {
                $expectedResult = $page::getArchivedFilterExpected($filterFieldValue);

                $url = $this->appendFilters($page::getUrl(), [$filterParameter => $filterFieldValue]);
                $I->prepareRequest();
                $I->sendGET($url);
                $I->seeJsonFieldContainsArrayValues("_embedded.".$page::EMBEDDED_RESOURCE, $expectedResult);
                $I->seeEmbeddedTypeIsOfSize($page::EMBEDDED_RESOURCE, count($expectedResult));
            }
        }
    }
}

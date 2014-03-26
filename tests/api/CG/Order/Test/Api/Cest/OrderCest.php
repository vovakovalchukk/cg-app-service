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
     * @group get
     * @group filter
     * @group p2
     **/
    public function testArrayFiltersDontAcceptString(ApiGuy $I)
    {
        $page = $this->getPageClass();
        $arrayFilterFields = array();
        foreach ($page::getFilterFields() as $filterField => $value) {
            if (is_array($value)) {
                $filterField = isset($page::getFilterMap()[$filterField]) ? $page::getFilterMap()[$filterField] : $filterField;
                $arrayFilterFields[] = $filterField;
            }
        }

        if (empty($arrayFilterFields) || !isset($page::allowedMethods()[$page::GET])) {
            $I->amGoingTo('skip testing array filters as GET is not an allowed method and / or there are no array filter fields.');
            return;
        }

        $I->wantTo('test array filters.');
        $testCollection = $this->getTestCollection();
        $url = $page::getUrl();

        $orderedTestCollection = array();
        foreach ($testCollection as $entity) {
            $orderedTestCollection[$entity["id"]] = $entity;
        }

        foreach ($arrayFilterFields as $arrayFilterField) {
            $filters = array(
                $arrayFilterField => $orderedTestCollection[
                $page::PRIMARY_ID
                ][
                $arrayFilterField]);
            $I->prepareRequest();
            $I->sendGET($this->appendFilters($url, $filters));
            $I->seeResponseCodeIs(HttpStatus::BAD_REQUEST);
        }
    }
    
    /**
     * @group get
     * @group filters
     * @group p2
     */
    public function checkMultiFilters(ApiGuy $I){
        $page = $this->getPageClass();
        $filterFields = $this->hasFilters($I);
        $testCollection = $this->getTestCollection();

        if(!$filterFields){
            return false;
        }

        $I->wantTo("check filters work for this collection");

        $multiFilterFields = array();
        foreach($filterFields as $key => $filterField){
            if(is_array($filterField)){
                $mappedKey = isset($page::getFilterMap()[$key]) ? $page::getFilterMap()[$key] : $key;
                $multiFilterFields[$key] = array();
                $multiFilterFields[$key][] = $testCollection[0][$mappedKey];
            }
        }

        if(empty($multiFilterFields)){
            $I->amGoingTo("skip multifilters as they are not required");
            return false;
        }

        $this->populateFilteredExpectedCollection($I, $page, $multiFilterFields);

        $url = $this->appendFilters($page::getUrl(), $multiFilterFields);
        $I->prepareRequest();
        $I->sendGET($url);
        $I->seeJsonFieldContainsArrayValues("_embedded.".$page::EMBEDDED_RESOURCE, $this->expected);
        $I->seeEmbeddedTypeIsOfSize($page::EMBEDDED_RESOURCE, count($this->expected));
    }

    protected function populateFilteredExpectedCollection(ApiGuy $I, $page, $filterFields)
    {
        $testCollection = $this->getTestCollection();
        $filters = array();
        foreach($filterFields as $key => $filterField) {
            if(!is_array($filterField)){
                $filterKey = $filterField;
                $mappedKey = isset($page::getFilterMap()[$filterKey]) ? $page::getFilterMap()[$filterKey] : $filterKey;
                $filters[$filterKey] = $testCollection[0][$mappedKey];
            }else{
                $filterKey = $key;
                $mappedKey = isset($page::getFilterMap()[$filterKey]) ? $page::getFilterMap()[$filterKey] : $filterKey;
                $filters[$filterKey] = array($testCollection[0][$mappedKey]);
            }
        }
        $expected = array();
        foreach ($testCollection as $entity) {
            $expected[$entity['id']] = $entity;
        }

        foreach($filters as $key => $filterField){
            $key = isset($page::getFilterMap()[$key]) ? $page::getFilterMap()[$key] : $key;
            foreach($testCollection as $testEntity){
                if (is_array($filterField)) {
                    if(!isset($testEntity[$key]) || !in_array($testEntity[$key], $filterField)) {
                        unset($expected[$testEntity['id']]);
                    }
                } else {
                    if(!isset($testEntity[$key]) || $testEntity[$key] != $filterField){
                        unset($expected[$testEntity['id']]);
                    }
                }
            }
        }
        $this->expected = $expected;
        $this->filters = $filters;
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

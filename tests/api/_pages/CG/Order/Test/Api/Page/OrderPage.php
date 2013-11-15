<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderItemPage;

class OrderPage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/order";
    const EMBEDDED_RESOURCE = "order";

    public static function getUrl(){
        return self::URL;
    }

    public static function notAllowedMethods(){
        return [
                static::POST    => static::POST,
                static::PUT    => static::PUT,
                static::DELETE => static::DELETE
        ];
    }

    public static function getTestCollection()
    {
        return [
                [
                    "id" => "1411-10",
                    "accountId" => 1411,
                    "externalId" => 10,
                    "channel" => "ebay",
                    "organisationUnitId" => 1,
                    "total" => 21.99,
                    "status" => 1,
                    "shippingPrice" => 10.99,
                    "shippingMethod" => "standard",
                    "currencyCode" => "GBP",
                    "billingAddress" => ["addressCompanyName" => "Company Name 1",
                                        "addressFullName" => "Full Name 1",
                                        "address1" => "address 1 - 1",
                                        "address2" => "address 2 - 1",
                                        "address3" => "address 3 - 1",
                                        "addressCity" => "City1",
                                        "addressCounty" => "County1",
                                        "addressCountry" => "Country1",
                                        "addressPostcode" => "Postcode1",
                                        "emailAddress" => "emailaddress1@channelgrabber.com",
                                        "phoneNumber" => "01942673431"],
                    "shippingAddress" => ["addressCompanyName" => "Shipping Company Name 1",
                                        "addressFullName" => "Full Name 1",
                                        "address1" => "shipping address 1 - 1",
                                        "address2" => "shipping address 2 - 1",
                                        "address3" => "shipping address 3 - 1",
                                        "addressCity" => "shipping City 1",
                                        "addressCounty" => "Shipping County 1",
                                        "addressCountry" => "United Kingdom",
                                        "addressPostcode" => "shipPostcode1",
                                        "emailAddress" => "shippingemail1@channelgrabber.com",
                                        "phoneNumber" => "07415878961"],
                    "totalDiscount" => 0.00,
                    "buyerMessage" => "Hello, please leave at the door",
                    "purchaseDate" => "2013-10-10 00:00:00",
                    "paymentDate" => "2013-10-10 01:00:00",
                    "printedDate" => "2013-10-10 10:00:00",
                    "dispatchDate" => "2013-10-10 10:00:10",
                    "archived" => false,
                    "custom" => ["username1" => "fakeebayusername1",
                                "email1" => "buyeremail1@fakeemailaddress.com"]
                ],
                [
                    "id" => "1411-20",
                    "accountId" => 1412,
                    "externalId" => 20,
                    "channel" => "ebay2",
                    "organisationUnitId" => 2,
                    "total" => 22.99,
                    "status" => 2,
                    "shippingPrice" => 20.99,
                    "shippingMethod" => "standard2",
                    "currencyCode" => "GBP2",
                    "billingAddress" => ["addressCompanyName" => "Company Name 2",
                                        "addressFullName" => "Full Name 2",
                                        "address1" => "address 1 - 2",
                                        "address2" => "address 2 - 2",
                                        "address3" => "address 3 - 2",
                                        "addressCity" => "City2",
                                        "addressCounty" => "County2",
                                        "addressCountry" => "Country2",
                                        "addressPostcode" => "Postcode2",
                                        "emailAddress" => "emailaddress2@channelgrabber.com",
                                        "phoneNumber" => "01942673432"],
                    "shippingAddress" => ["addressCompanyName" => "Shipping Company Name 2",
                                        "addressFullName" => "Full Name 2",
                                        "address1" => "shipping address 1 - 2",
                                        "address2" => "shipping address 2 - 2",
                                        "address3" => "shipping address 3 - 2",
                                        "addressCity" => "shipping City 2",
                                        "addressCounty" => "Shipping County 2",
                                        "addressCountry" => "United Kingdom",
                                        "addressPostcode" => "shipPostcode2",
                                        "emailAddress" => "shippingemail2@channelgrabber.com",
                                        "phoneNumber" => "07415878962"],
                    "totalDiscount" => 0.02,
                    "buyerMessage" => "Hello, please leave at the door2",
                    "purchaseDate" => "2013-10-10 00:20:00",
                    "paymentDate" => "2013-10-10 01:20:00",
                    "printedDate" => "2013-10-10 10:20:00",
                    "dispatchDate" => "2013-10-10 10:20:10",
                    "archived" => false,
                    "custom" => ["username2" => "fakeebayusername2",
                                "email2" => "buyeremail2@fakeemailaddress.com"]
                ],
                [
                    "id" => "1411-30",
                    "accountId" => 1411,
                    "externalId" => 30,
                    "channel" => "ebay",
                    "organisationUnitId" => 1,
                    "total" => 23.99,
                    "status" => 1,
                    "shippingPrice" => 30.99,
                    "shippingMethod" => "standard",
                    "currencyCode" => "GBP3",
                    "billingAddress" => ["addressCompanyName" => "Company Name 3",
                                        "addressFullName" => "Full Name 3",
                                        "address1" => "address 1 - 3",
                                        "address2" => "address 2 - 3",
                                        "address3" => "address 3 - 3",
                                        "addressCity" => "City3",
                                        "addressCounty" => "County3",
                                        "addressCountry" => "Country3",
                                        "addressPostcode" => "Postcode3",
                                        "emailAddress" => "emailaddress3@channelgrabber.com",
                                        "phoneNumber" => "01942673433"],
                    "shippingAddress" => ["addressCompanyName" => "Shipping Company Name 3",
                                        "addressFullName" => "Full Name 3",
                                        "address1" => "shipping address 1 - 3",
                                        "address2" => "shipping address 2 - 3",
                                        "address3" => "shipping address 3 - 3",
                                        "addressCity" => "shipping City 3",
                                        "addressCounty" => "Shipping County 3",
                                        "addressCountry" => "United Kingdom",
                                        "addressPostcode" => "shipPostcode3",
                                        "emailAddress" => "shippingemail3@channelgrabber.com",
                                        "phoneNumber" => "07415878963"],
                    "totalDiscount" => 0.03,
                    "buyerMessage" => "Hello, please leave at the door3",
                    "purchaseDate" => "2013-10-10 00:30:00",
                    "paymentDate" => "2013-10-10 01:30:00",
                    "printedDate" => "2013-10-10 10:30:00",
                    "dispatchDate" => "2013-10-10 10:30:10",
                    "archived" => true,
                    "custom" => ["username3" => "fakeebayusername3",
                                 "email3" => "buyeremail3@fakeemailaddress.com"]
                ],
                [
                    "id" => "1414-40",
                    "accountId" => 1414,
                    "externalId" => 40,
                    "channel" => "ebay4",
                    "organisationUnitId" => 4,
                    "total" => 24.99,
                    "status" => 4,
                    "shippingPrice" => 40.99,
                    "shippingMethod" => "standard4",
                    "currencyCode" => "GBP4",
                    "billingAddress" => ["addressCompanyName" => "Company Name 4",
                                        "addressFullName" => "Full Name 4",
                                        "address1" => "address 1 - 4",
                                        "address2" => "address 2 - 4",
                                        "address3" => "address 3 - 4",
                                        "addressCity" => "City4",
                                        "addressCounty" => "County4",
                                        "addressCountry" => "Country4",
                                        "addressPostcode" => "Postcode4",
                                        "emailAddress" => "emailaddress4@channelgrabber.com",
                                        "phoneNumber" => "01942673434"],
                    "shippingAddress" => ["addressCompanyName" => "Shipping Company Name 4",
                                        "addressFullName" => "Full Name 4",
                                        "address1" => "shipping address 1 - 4",
                                        "address2" => "shipping address 2 - 4",
                                        "address3" => "shipping address 3 - 4",
                                        "addressCity" => "shipping City 4",
                                        "addressCounty" => "Shipping County 4",
                                        "addressCountry" => "France",
                                        "addressPostcode" => "shipPostcode4",
                                        "emailAddress" => "shippingemail4@channelgrabber.com",
                                        "phoneNumber" => "07415878964"],
                    "totalDiscount" => 0.04,
                    "buyerMessage" => "Hello, please leave at the door4",
                    "purchaseDate" => "2013-10-10 00:40:00",
                    "paymentDate" => "2013-10-10 01:40:00",
                    "printedDate" => "2013-10-10 10:40:00",
                    "dispatchDate" => "2013-10-10 10:40:10",
                    "archived" => true,
                    "custom" => ["username4" => "fakeebayusername4",
                                "email4" => "buyeremail4@fakeemailaddress.com"]
                ],
                [
                    "id" => "1415-50",
                    "accountId" => 1415,
                    "externalId" => 50,
                    "channel" => "ebay5",
                    "organisationUnitId" => 5,
                    "total" => 25.99,
                    "status" => 5,
                    "shippingPrice" => 50.99,
                    "shippingMethod" => "standard5",
                    "currencyCode" => "GBP5",
                    "billingAddress" => ["addressCompanyName" => "Company Name 5",
                                        "addressFullName" => "Full Name 5",
                                        "address1" => "address 1 - 5",
                                        "address2" => "address 2 - 5",
                                        "address3" => "address 3 - 5",
                                        "addressCity" => "City5",
                                        "addressCounty" => "County5",
                                        "addressCountry" => "Country5",
                                        "addressPostcode" => "Postcode5",
                                        "emailAddress" => "emailaddress5@channelgrabber.com",
                                        "phoneNumber" => "01942673435"],
                    "shippingAddress" => ["addressCompanyName" => "Shipping Company Name 5",
                                        "addressFullName" => "Full Name 5",
                                        "address1" => "shipping address 1 - 5",
                                        "address2" => "shipping address 2 - 5",
                                        "address3" => "shipping address 3 - 5",
                                        "addressCity" => "shipping City 5",
                                        "addressCounty" => "Shipping County 5",
                                        "addressCountry" => "France",
                                        "addressPostcode" => "shipPostcode5",
                                        "emailAddress" => "shippingemail5@channelgrabber.com",
                                        "phoneNumber" => "07415878965"],
                    "totalDiscount" => 0.05,
                    "buyerMessage" => "Hello, please leave at the door5",
                    "purchaseDate" => "2013-10-10 00:50:00",
                    "paymentDate" => "2013-10-10 01:50:00",
                    "printedDate" => "2013-10-10 10:50:00",
                    "dispatchDate" => "2013-10-10 10:50:10",
                    "archived" => true,
                    "custom" => ["username5" => "fakeebayusername5",
                                "email5" => "buyeremail5@fakeemailaddress.com"]
                ],
        ];
    }

    public static function getRequiredEntityFields(){
        return ["accountId",
                "channel",
                "organisationUnitId",
                "total",
                "status",
                "shippingPrice",
                "shippingMethod",
                "billingAddress" => ["addressFullName",
                                    "address1",
                                    "addressCity",
                                    "addressCounty",
                                    "addressCountry",
                                    "addressPostcode",
                                    "emailAddress"],
                "shippingAddress" => ["addressFullName",
                                     "address1",
                                     "addressCity",
                                     "addressCounty",
                                     "addressCountry",
                                     "addressPostcode",
                                     "emailAddress"],
                "totalDiscount",
                "archived",
                "custom" => []
        ];
    }

    public static function getInvalidEntityData(){
        return ["accountId" => "ABC",
                "channel" => [],
                "organisationUnitId" => "ABC",
                "total" => "ABC",
                "status" => [],
                "shippingPrice" => "ABC",
                "shippingMethod" => [],
                "billingAddress" => ["addressFullName" => [],
                                    "address1" => [],
                                    "addressCity" => [],
                                    "addressCounty" => [],
                                    "addressCountry" => [],
                                    "addressPostcode" => [],
                                    "emailAddress" => []],
                "shippingAddress" => ["addressFullName" => [],
                                     "address1" => [],
                                     "addressCity" => [],
                                     "addressCounty" => [],
                                     "addressCountry" => [],
                                     "addressPostcode" => [],
                                     "emailAddress" => []],
                "totalDiscount" => "ABC",
                "archived" => [],
                "custom" => "ABC"
        ];
    }

    public static function getInvalidEntityFields(){
        return ["accountId",
                "channel",
                "organisationUnitId",
                "total",
                "status",
                "shippingPrice",
                "shippingMethod",
                "billingAddress" => ["addressFullName",
                                    "address1",
                                    "addressCity",
                                    "addressCounty",
                                    "addressCountry",
                                    "addressPostcode",
                                    "emailAddress"],
                "shippingAddress" => ["addressFullName",
                                    "address1",
                                    "addressCity",
                                    "addressCounty",
                                    "addressCountry",
                                    "addressPostcode",
                                    "emailAddress"],
                "totalDiscount",
                "archived",
                "custom" => []
        ];
    }

    public static function getFilterFields()
    {
        return ["id" => [],
                "organisationUnitId" => [],
                "status" => [],
                "accountId" => [],
                "channel" => [],
                "archived",
                "shippingMethod" => []
        ];
    }

    public static function getRangeFilters()
    {
        return [["field" => "purchaseDate", "startFilter" => "timeFrom", "endFilter" => "timeTo"]];
    }

    public static function getCountryFilter()
    {
        $testEntity = static::getTestCollection()[0];
        return ["country" => $testEntity['shippingAddress']['addressCountry']];
    }

    public static function getCountryFilterExpected()
    {
        $testOrderCollection = static::getTestCollection();
        $filter = static::getCountryFilter();

        $expectedResult = [];
        foreach ($testOrderCollection as $testOrder) {
            if ($testOrder["shippingAddress"]["addressCountry"] == $filter["country"]) {
                $expectedResult[] = $testOrder;
            }
        }

        return $expectedResult;
    }

    public static function getCountryExcludeFilter()
    {
        $testEntity = static::getTestCollection()[4];
        return ["countryExclude" => $testEntity['shippingAddress']['addressCountry']];
    }

    public static function getCountryExcludeFilterExpected()
    {
        $testOrderCollection = static::getTestCollection();
        $filter = static::getCountryExcludeFilter();

        $expectedResult = [];
        foreach ($testOrderCollection as $testOrder) {
            if ($testOrder["shippingAddress"]["addressCountry"] != $filter["countryExclude"]) {
                $expectedResult[] = $testOrder;
            }
        }

        return $expectedResult;
    }

    public static function getMultiLineFilter()
    {
        return ["multiLineOrder" => [true, false]];
    }

    public static function getMultiLineFilterExpected($isTrue)
    {
        $testOrderItemCollection = OrderItemPage::getTestCollection();

        $testOrdersArray = [];
        foreach ($testOrderItemCollection as $testOrderItem) {
            $testOrdersArray[$testOrderItem['orderId']][] = $testOrderItem;
        }

        $testOrdersExpected = [];
        foreach ($testOrdersArray as $key => $testOrder) {
            if (count($testOrder) > 1) {
                $testOrdersExpected[$key] = true;
            } else {
                $testOrdersExpected[$key] = false;
            }
        }

        $testOrderCollection = static::getTestCollection();

        $expectedResult = [];
        foreach ($testOrderCollection as $testOrder) {
            if ((!isset($testOrdersExpected[$testOrder['id']]) && !$isTrue)
                || (isset($testOrdersExpected[$testOrder['id']]) && ($testOrdersExpected[$testOrder['id']] == $isTrue))) {
                $expectedResult[] = $testOrder;
            }
        }

        return $expectedResult;
    }

    public static function getMultiSameItemFilter()
    {
        return ["multiSameItem" => [true, false]];
    }

    public static function getMultiSameItemFilterExpected($isTrue)
    {
        $testOrderItemCollection = OrderItemPage::getTestCollection();

        $testOrdersExpected = [];
        foreach ($testOrderItemCollection as $testOrderItem) {
            if ($testOrderItem['itemQuantity'] > 1) {
                $testOrdersExpected[$testOrderItem['orderId']] = true;
            } else if (!isset($testOrdersExpected[$testOrderItem['orderId']])) {
                $testOrdersExpected[$testOrderItem['orderId']] = false;
            }
        }

        $testOrderCollection = static::getTestCollection();

        $expectedResult = [];
        foreach ($testOrderCollection as $testOrder) {
            if ((!isset($testOrdersExpected[$testOrder['id']]) && !$isTrue)
                || (isset($testOrdersExpected[$testOrder['id']]) && ($testOrdersExpected[$testOrder['id']] == $isTrue))) {
                $expectedResult[] = $testOrder;
            }
        }

        return $expectedResult;
    }
}
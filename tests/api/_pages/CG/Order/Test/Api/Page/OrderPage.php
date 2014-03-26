<?php
namespace CG\Order\Test\Api\Page;

use CG\Codeception\Cest\Rest\CollectionPageTrait;
use CG\Order\Test\Api\Page\OrderItemPage;

class OrderPage extends RootPage
{
    use CollectionPageTrait;

    const URL = "/order";
    const EMBEDDED_RESOURCE = "order";
    const PRIMARY_ID = "1411-10";
    const SECONDARY_ID = "1412-20";

    public static function getUrl()
    {
        return self::URL;
    }

    public static function notAllowedMethods()
    {
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
                    "externalId" => "10",
                    "channel" => "ebay",
                    "organisationUnitId" => 1,
                    "total" => 21.99,
                    "status" => "1",
                    "shippingPrice" => 10.99,
                    "shippingMethod" => "standard",
                    "currencyCode" => "GBP",
                    "billingAddressCompanyName" => "Company Name 1",
                    "billingAddressFullName" => "Full Name 1",
                    "billingAddress1" => "address 1 - 1",
                    "billingAddress2" => "address 2 - 1",
                    "billingAddress3" => "address 3 - 1",
                    "billingAddressCity" => "City1",
                    "billingAddressCounty" => "County1",
                    "billingAddressCountry" => "UK",
                    "billingAddressPostcode" => "Postcode1",
                    "billingEmailAddress" => "emailaddress1@channelgrabber.com",
                    "billingPhoneNumber" => "01942673431",
                    "billingAddressCountryCode" => "GB",
                    "shippingAddressCompanyName" => "Shipping Company Name 1",
                    "shippingAddressFullName" => "Full Name 1",
                    "shippingAddress1" => "shipping address 1 - 1",
                    "shippingAddress2" => "shipping address 2 - 1",
                    "shippingAddress3" => "shipping address 3 - 1",
                    "shippingAddressCity" => "shipping City 1",
                    "shippingAddressCounty" => "Shipping County 1",
                    "shippingAddressCountry" => "UK",
                    "shippingAddressPostcode" => "shipPostcode1",
                    "shippingEmailAddress" => "shippingemail1@channelgrabber.com",
                    "shippingPhoneNumber" => "07415878961",
                    "shippingAddressCountryCode" => "GB",
                    "totalDiscount" => 0.00,
                    "buyerMessage" => "Hello, please leave at the door",
                    "purchaseDate" => "2013-10-10 00:00:00",
                    "paymentDate" => "2013-10-10 01:00:00",
                    "printedDate" => "2013-10-10 10:00:00",
                    "dispatchDate" => "2013-10-10 10:00:10",
                    "archived" => false,
                    "tag" => [
                        "tag1",
                        "tag2",
                        "tag5"
                    ],
                    "custom" => [],
                    "batch" => 1,
                    "paymentMethod" => "paymentMethod1",
                    "paymentReference" => "paymentReference1"
                ],
                [
                    "id" => "1412-20",
                    "accountId" => 1412,
                    "externalId" => "20",
                    "channel" => "ebay2",
                    "organisationUnitId" => 2,
                    "total" => 22.99,
                    "status" => "2",
                    "shippingPrice" => 20.99,
                    "shippingMethod" => "standard2",
                    "currencyCode" => "GBP",
                    "billingAddressCompanyName" => "Company Name 2",
                    "billingAddressFullName" => "Full Name 2",
                    "billingAddress1" => "address 1 - 2",
                    "billingAddress2" => "address 2 - 2",
                    "billingAddress3" => "address 3 - 2",
                    "billingAddressCity" => "City2",
                    "billingAddressCounty" => "County2",
                    "billingAddressCountry" => "UK",
                    "billingAddressPostcode" => "Postcode2",
                    "billingEmailAddress" => "emailaddress2@channelgrabber.com",
                    "billingPhoneNumber" => "01942673432",
                    "billingAddressCountryCode" => "GB",
                    "shippingAddressCompanyName" => "Shipping Company Name 2",
                    "shippingAddressFullName" => "Full Name 2",
                    "shippingAddress1" => "shipping address 1 - 2",
                    "shippingAddress2" => "shipping address 2 - 2",
                    "shippingAddress3" => "shipping address 3 - 2",
                    "shippingAddressCity" => "shipping City 2",
                    "shippingAddressCounty" => "Shipping County 2",
                    "shippingAddressCountry" => "UK",
                    "shippingAddressPostcode" => "shipPostcode2",
                    "shippingEmailAddress" => "shippingemail2@channelgrabber.com",
                    "shippingPhoneNumber" => "07415878962",
                    "shippingAddressCountryCode" => "GB",
                    "totalDiscount" => 0.02,
                    "buyerMessage" => "Hello, please leave at the door2",
                    "purchaseDate" => "2013-10-10 00:20:00",
                    "paymentDate" => "2013-10-10 01:20:00",
                    "printedDate" => "2013-10-10 10:20:00",
                    "dispatchDate" => "2013-10-10 10:20:10",
                    "archived" => false,
                    "tag" => [
                        "tag2",
                        "tag3"
                    ],
                    "custom" => [],
                    "batch" => 1,
                    "paymentMethod" => "paymentMethod2",
                    "paymentReference" => "paymentReference2"
                ],
                [
                    "id" => "1413-30",
                    "accountId" => 1413,
                    "externalId" => "30",
                    "channel" => "ebay3",
                    "organisationUnitId" => 3,
                    "total" => 23.99,
                    "status" => "3",
                    "shippingPrice" => 30.99,
                    "shippingMethod" => "standard3",
                    "currencyCode" => "GBP",
                    "billingAddressCompanyName" => "Company Name 3",
                    "billingAddressFullName" => "Full Name 3",
                    "billingAddress1" => "address 1 - 3",
                    "billingAddress2" => "address 2 - 3",
                    "billingAddress3" => "address 3 - 3",
                    "billingAddressCity" => "City3",
                    "billingAddressCounty" => "County3",
                    "billingAddressCountry" => "UK",
                    "billingAddressPostcode" => "Postcode3",
                    "billingEmailAddress" => "emailaddress3@channelgrabber.com",
                    "billingPhoneNumber" => "01942673433",
                    "billingAddressCountryCode" => "GB",
                    "shippingAddressCompanyName" => "Shipping Company Name 3",
                    "shippingAddressFullName" => "Full Name 3",
                    "shippingAddress1" => "shipping address 1 - 3",
                    "shippingAddress2" => "shipping address 2 - 3",
                    "shippingAddress3" => "shipping address 3 - 3",
                    "shippingAddressCity" => "shipping City 3",
                    "shippingAddressCounty" => "Shipping County 3",
                    "shippingAddressCountry" => "UK",
                    "shippingAddressPostcode" => "shipPostcode3",
                    "shippingEmailAddress" => "shippingemail3@channelgrabber.com",
                    "shippingPhoneNumber" => "07415878963",
                    "shippingAddressCountryCode" => "GB",
                    "totalDiscount" => 0.03,
                    "buyerMessage" => "Hello, please leave at the door3",
                    "purchaseDate" => "2013-10-10 00:30:00",
                    "paymentDate" => "2013-10-10 01:30:00",
                    "printedDate" => "2013-10-10 10:30:00",
                    "dispatchDate" => "2013-10-10 10:30:10",
                    "archived" => false,
                    "tag" => [
                        "tag3",
                        "tag4"
                    ],
                    "custom" => [],
                    "batch" => 1,
                    "paymentMethod" => "paymentMethod3",
                    "paymentReference" => "paymentReference3"
                ],
                [
                    "id" => "1414-40",
                    "accountId" => 1414,
                    "externalId" => "40",
                    "channel" => "ebay4",
                    "organisationUnitId" => 4,
                    "total" => 24.99,
                    "status" => "4",
                    "shippingPrice" => 40.99,
                    "shippingMethod" => "standard4",
                    "currencyCode" => "GBP",
                    "billingAddressCompanyName" => "Company Name 4",
                    "billingAddressFullName" => "Full Name 4",
                    "billingAddress1" => "address 1 - 4",
                    "billingAddress2" => "address 2 - 4",
                    "billingAddress3" => "address 3 - 4",
                    "billingAddressCity" => "City4",
                    "billingAddressCounty" => "County4",
                    "billingAddressCountry" => "UK",
                    "billingAddressPostcode" => "Postcode4",
                    "billingEmailAddress" => "emailaddress4@channelgrabber.com",
                    "billingPhoneNumber" => "01942673434",
                    "billingAddressCountryCode" => "GB",
                    "shippingAddressCompanyName" => "Shipping Company Name 4",
                    "shippingAddressFullName" => "Full Name 4",
                    "shippingAddress1" => "shipping address 1 - 4",
                    "shippingAddress2" => "shipping address 2 - 4",
                    "shippingAddress3" => "shipping address 3 - 4",
                    "shippingAddressCity" => "shipping City 4",
                    "shippingAddressCounty" => "Shipping County 4",
                    "shippingAddressCountry" => "UK",
                    "shippingAddressPostcode" => "shipPostcode4",
                    "shippingEmailAddress" => "shippingemail4@channelgrabber.com",
                    "shippingPhoneNumber" => "07415878964",
                    "shippingAddressCountryCode" => "GB",
                    "totalDiscount" => 0.04,
                    "buyerMessage" => "Hello, please leave at the door4",
                    "purchaseDate" => "2013-10-10 00:40:00",
                    "paymentDate" => "2013-10-10 01:40:00",
                    "printedDate" => "2013-10-10 10:40:00",
                    "dispatchDate" => "2013-10-10 10:40:10",
                    "archived" => false,
                    "tag" => [
                        "tag4",
                        "tag5"
                    ],
                    "custom" => [],
                    "batch" => 1,
                    "paymentMethod" => "paymentMethod4",
                    "paymentReference" => "paymentReference4"
                ],
                [
                    "id" => "1415-50",
                    "accountId" => 1415,
                    "externalId" => "50",
                    "channel" => "ebay5",
                    "organisationUnitId" => 5,
                    "total" => 25.99,
                    "status" => "5",
                    "shippingPrice" => 50.99,
                    "shippingMethod" => "standard5",
                    "currencyCode" => "GBP",
                    "billingAddressCompanyName" => "Company Name 5",
                    "billingAddressFullName" => "Full Name 5",
                    "billingAddress1" => "address 1 - 5",
                    "billingAddress2" => "address 2 - 5",
                    "billingAddress3" => "address 3 - 5",
                    "billingAddressCity" => "City5",
                    "billingAddressCounty" => "County5",
                    "billingAddressCountry" => "France",
                    "billingAddressPostcode" => "Postcode5",
                    "billingEmailAddress" => "emailaddress5@channelgrabber.com",
                    "billingPhoneNumber" => "01942673435",
                    "billingAddressCountryCode" => "FR",
                    "shippingAddressCompanyName" => "Shipping Company Name 5",
                    "shippingAddressFullName" => "Full Name 5",
                    "shippingAddress1" => "shipping address 1 - 5",
                    "shippingAddress2" => "shipping address 2 - 5",
                    "shippingAddress3" => "shipping address 3 - 5",
                    "shippingAddressCity" => "shipping City 5",
                    "shippingAddressCounty" => "Shipping County 5",
                    "shippingAddressCountry" => "France",
                    "shippingAddressPostcode" => "shipPostcode5",
                    "shippingEmailAddress" => "shippingemail5@channelgrabber.com",
                    "shippingPhoneNumber" => "07415878965",
                    "shippingAddressCountryCode" => "FR",
                    "totalDiscount" => 0.05,
                    "buyerMessage" => "Hello, please leave at the door5",
                    "purchaseDate" => "2013-10-10 00:50:00",
                    "paymentDate" => "2013-10-10 01:50:00",
                    "printedDate" => "2013-10-10 10:50:00",
                    "dispatchDate" => "2013-10-10 10:50:10",
                    "archived" => false,
                    "tag" => [
                    ],
                    "custom" => [],
                    "batch" => 2,
                    "paymentMethod" => "paymentMethod5",
                    "paymentReference" => "paymentReference5"
                ]
        ];
    }

    public static function getRequiredEntityFields()
    {
        return ["accountId",
                "externalId",
                "channel",
                "organisationUnitId",
                "total",
                "status",
                "totalDiscount",
                "shippingPrice",
                "shippingMethod",
                "currencyCode",
                "purchaseDate",
                "batch"
        ];
    }

    public static function getInvalidEntityData()
    {
        return ["accountId" => "abc",
            "externalId" => [],
            "channel" => [],
            "organisationUnitId" => "abc",
            "total" => "abc",
            "status" => [],
            "totalDiscount" => "abc",
            "shippingPrice" => "abc",
            "shippingMethod" => [],
            "currencyCode" => "INVALIDCURRENCYCODE",
            "purchaseDate" => [],
            "batch" => []
        ];
    }

    public static function getInvalidEntityFields()
    {
        return ["accountId",
                "externalId",
                "channel",
                "organisationUnitId",
                "total",
                "status",
                "totalDiscount",
                "shippingPrice",
                "shippingMethod",
                "currencyCode",
                "purchaseDate"
        ];
    }

    public static function getFilterMap()
    {
        return [
            "orderIds" => "id"
        ];
    }

    public static function getFilterFields()
    {
        return [
            "orderIds" => [],
            "organisationUnitId" => [],
            "status" => [],
            "accountId" => [],
            "channel" => [],
            //"includeArchived",  Not working atm
            "shippingMethod" => [],
            "batch" => []
        ];
    }

    public static function getRangeFilters()
    {
        return [["field" => "purchaseDate", "startFilter" => "timeFrom", "endFilter" => "timeTo"]];
    }

    public static function getCountryFilter()
    {
        $testEntity = static::getTestCollection()[0];
        return ["country" => array($testEntity['shippingAddressCountryCode'])];
    }

    public static function getCountryFilterExpected()
    {
        $testOrderCollection = static::getTestCollection();
        $filter = static::getCountryFilter();

        $expectedResult = [];
        foreach ($testOrderCollection as $testOrder) {
            if (in_array($testOrder["shippingAddressCountryCode"], $filter["country"])) {
                $expectedResult[] = $testOrder;
            }
        }

        return $expectedResult;
    }

    public static function getCountryExcludeFilter()
    {
        $testEntity = static::getTestCollection()[4];
        return ["countryExclude" => array($testEntity['shippingAddressCountryCode'])];
    }

    public static function getCountryExcludeFilterExpected()
    {
        $testOrderCollection = static::getTestCollection();
        $filter = static::getCountryExcludeFilter();

        $expectedResult = [];
        foreach ($testOrderCollection as $testOrder) {
            if (!in_array($testOrder["shippingAddressCountryCode"], $filter["countryExclude"])) {
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

    public static function getArchiveFilter()
    {
        return ["includeArchived" => "archived"];
    }

    public static function getArchivedFilterExpected($filterValue)
    {
        $testCollection = static::getTestCollection();

        $archiveFilter = static::getArchiveFilter();

        $expectedResult = [];
        foreach ($archiveFilter as $filterField) {
            foreach ($testCollection as $testEntity) {
                if ($testEntity[$filterField] == $filterValue) {
                    $expectedResult[] = $testEntity;
                }
            }
        }

        return $expectedResult;
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

    public static function getSearchTermFilterFields()
    {
        return ["externalId",
                "shippingMethod",
                "billingAddressFullName",
                "billingAddress1",
                "billingAddressCity",
                "billingAddressCounty",
                "billingAddressCountry",
                "billingAddressPostcode",
                "billingEmailAddress",
                "shippingAddressFullName",
                "shippingAddress1",
                "shippingAddressCity",
                "shippingAddressCounty",
                "shippingAddressCountry",
                "shippingAddressPostcode",
                "shippingEmailAddress",
                "buyerMessage",
                "item[].itemSku",
                "item[].itemName"
        ];
    }

    public static function getChildPageClass()
    {
        return OrderItemPage::class;
    }

    //Lazyness at its peak
    public static function generateDumpFromTestData()
    {
        foreach (OrderPage::getTestCollection() as $entity) {

            foreach ($entity as $field => $value) {
                if (is_array($value) || $field == "archived") {
                    $$field = $value;
                    unset($entity[$field]);
                }
            }

            echo "INSERT INTO `order` (`" . implode("`, `", array_keys($entity)) . "`) VALUES ('";
            echo @implode("', '", $entity) . "');\n";

            echo "INSERT INTO `address` (`" . implode("`, `", array_keys($billingAddress)) . "`) VALUES ('";
            echo @implode("', '", $billingAddress) . "');\n";

            echo "INSERT INTO `address` (`" . implode("`, `", array_keys($shippingAddress)) . "`) VALUES ('";
            echo @implode("', '", $shippingAddress) . "');\n";
        }
    }
}

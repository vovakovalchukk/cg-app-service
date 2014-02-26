<?php

use Phinx\Migration\AbstractMigration;
require_once __DIR__.'/../InsertTestDataTrait.php';

class TestData extends AbstractMigration
{
    use InsertTestDataTrait;

    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
//        $this->insertTestData('service', $this->getServiceData());
//        $this->insertTestData('serviceEvent', $this->getServiceEventData());
        $this->insertTestData('order', $this->getOrderData());
        $this->insertTestData('address', $this->getAddressData());
        $this->insertTestData('orderTag', $this->getOrderTagData());
        $this->insertTestData('note', $this->getNoteData());
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
//        $this->execute('TRUNCATE table `service`');
//        $this->execute('TRUNCATE table `serviceEvent`');
        $this->execute('TRUNCATE table `order`');
        $this->execute('TRUNCATE table `address`');
        $this->execute('TRUNCATE table `orderTag`');
        $this->execute('TRUNCATE table `note`');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    //TODO Table doesn't exist
    protected function getServiceData()
    {
        return array(
            [1,'Type1','endpoint1'],
            [2,'Type2','endpoint2'],
            [3,'Type3','endpoint3'],
            [4,'Type4','endpoint4'],
            [5,'Type5','endpoint5']
        );
    }

    //TODO Table doesn't exist
    protected function getServiceEventData() {
        return array(
            [1,1,'type1',1,'http://example1.com'],
            [2,1,'type2',2,'http://example2.com'],
            [3,1,'type3',3,'http://example3.com'],
            [4,1,'type4',4,'http://example4.com'],
            [5,1,'type5',5,'http://example5.com'],
            [6,2,'type6',6,'http://example6.com']
        );
    }

    protected function getOrderData()
    {
        return array(
            ['1411-10', '1411', '10', 'ebay', '1', '21.99', '1', '10.99', 'standard', 'GBP', '0', 'Hello, please leave at the door',
             '2013-10-10 00:00:00', '2013-10-10 01:00:00', '2013-10-10 10:00:00', '2013-10-10 10:00:10', 1, 2, 1, 'paymentMethod1', 'paymentReference1'],
            ['1412-20', '1412', '20', 'ebay2', '2', '22.99', '2', '20.99', 'standard2', 'GBP', '0.02', 'Hello, please leave at the door2',
             '2013-10-10 00:20:00', '2013-10-10 01:20:00', '2013-10-10 10:20:00', '2013-10-10 10:20:10', 3, 4, 1, 'paymentMethod2', 'paymentReference2'],
            ['1413-30', '1413', '30', 'ebay3', '3', '23.99', '3', '30.99', 'standard3', 'GBP', '0.03', 'Hello, please leave at the door3',
             '2013-10-10 00:30:00', '2013-10-10 01:30:00', '2013-10-10 10:30:00', '2013-10-10 10:30:10', 5, 6, 1, 'paymentMethod3', 'paymentReference3'],
            ['1414-40', '1414', '40', 'ebay4', '4', '24.99', '4', '40.99', 'standard4', 'GBP', '0.04', 'Hello, please leave at the door4', '2013-10-10 00:40:00', '2013-10-10 01:40:00', '2013-10-10 10:40:00', '2013-10-10 10:40:10', 7, 8, 1, 'paymentMethod4', 'paymentReference4']
        );
    }

    protected function getAddressData()
    {
        return array(
            [1, 'Company Name 1', 'Full Name 1', 'address 1 - 1', 'address 2 - 1', 'address 3 - 1', 'City1', 'County1',
                'UK', 'Postcode1', 'emailaddress1@channelgrabber.com', '01942673431', 'GB'],
            [2, 'Shipping Company Name 1', 'Full Name 1', 'shipping address 1 - 1', 'shipping address 2 - 1', 'shipping address 3 - 1', 'shipping City 1', 'Shipping County 1',
                'UK', 'shipPostcode1', 'shippingemail1@channelgrabber.com', '07415878961', 'GB'],
            [3, 'Company Name 2', 'Full Name 2', 'address 1 - 2', 'address 2 - 2', 'address 3 - 2', 'City2', 'County2', 'UK', 'Postcode2',
                'emailaddress2@channelgrabber.com', '01942673432', 'GB'],
            [4, 'Shipping Company Name 2', 'Full Name 2', 'shipping address 1 - 2', 'shipping address 2 - 2', 'shipping address 3 - 2', 'shipping City 2',
                'Shipping County 2', 'UK', 'shipPostcode2', 'shippingemail2@channelgrabber.com', '07415878962', 'GB'],
            [5, 'Company Name 3', 'Full Name 3', 'address 1 - 3', 'address 2 - 3', 'address 3 - 3', 'City3', 'County3', 'UK', 'Postcode3',
                'emailaddress3@channelgrabber.com', '01942673433', 'GB'],
            [6, 'Shipping Company Name 3', 'Full Name 3', 'shipping address 1 - 3', 'shipping address 2 - 3', 'shipping address 3 - 3', 'shipping City 3',
                'Shipping County 3', 'UK', 'shipPostcode3', 'shippingemail3@channelgrabber.com', '07415878963', 'GB'],
            [7, 'Company Name 4', 'Full Name 4', 'address 1 - 4', 'address 2 - 4', 'address 3 - 4', 'City4', 'County4', 'UK', 'Postcode4',
                'emailaddress4@channelgrabber.com', '01942673434', 'GB'],
            [8, 'Shipping Company Name 4', 'Full Name 4', 'shipping address 1 - 4', 'shipping address 2 - 4', 'shipping address 3 - 4', 'shipping City 4', 'Shipping County 4',
                'UK', 'shipPostcode4', 'shippingemail4@channelgrabber.com', '07415878964', 'GB'],
            [9, 'Company Name 5', 'Full Name 5', 'address 1 - 5', 'address 2 - 5', 'address 3 - 5', 'City5', 'County5', 'France', 'Postcode5',
                'emailaddress5@channelgrabber.com', '01942673435', 'FR'],
            [10, 'Shipping Company Name 5', 'Full Name 5', 'shipping address 1 - 5', 'shipping address 2 - 5', 'shipping address 3 - 5', 'shipping City 5',
                'Shipping County 5', 'France', 'shipPostcode5', 'shippingemail5@channelgrabber.com', '07415878965', 'FR']
        );
    }

    protected function getOrderTagData()
    {
        return array(
            ['1411-10-tag1','1411-10', 'tag1', 1],
            ['1411-10-tag2','1411-10', 'tag2', 1],
            ['1411-10-tag5','1411-10', 'tag5', 1],
            ['1412-20-tag2','1412-20', 'tag2', 2],
            ['1412-20-tag3','1412-20', 'tag3', 2],
            ['1413-30-tag3','1413-30', 'tag3', 3],
            ['1413-30-tag4','1413-30', 'tag4', 3],
            ['1414-40-tag4','1414-40', 'tag4', 4],
            ['1414-40-tag5','1414-40', 'tag5', 4]
        );
    }

    protected function getNoteData()
    {
        return array(
            ['1411-10', 1, 'Note 1', '2013-10-10 01:00:00'],
            ['1411-10', 2, 'Note 2', '2013-10-10 02:00:00'],
            ['1411-10', 3, 'Note 3', '2013-10-10 03:00:00'],
            ['1411-10', 4, 'Note 4', '2013-10-10 04:00:00'],
            ['1412-20', 5, 'Note 5', '2013-10-10 05:00:00'],
            ['1411-10', 6, 'Note 6', '2013-10-10 06:00:00']
        );
    }
}
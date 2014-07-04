<?php

use Phinx\Migration\TestMigration;

class TestDataShippingMethod extends TestMigration
{
    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->insertTestData('shippingMethod', $this->getShippingMethodData());
        $this->insertTestData('shippingMethodExposure', $this->getShippingMethodExposureData());
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute('TRUNCATE table `shippingMethodExposure`');
        $this->execute('TRUNCATE table `shippingMethod`');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function getShippingMethodData()
    {
        return [
            [1, 'ebay', 'firstclass'],
            [2, 'amazon', 'firstclass'],
            [3, 'ebay', 'secondclass'],
            [4, 'amazon', 'secondclass'],
            [5, 'webstore', 'someclass'],
            [6, 'amazon', 'prime'],
            [7, 'ebay', 'nextyear']
        ];
    }

    public function getShippingMethodExposureData()
    {
        return [
            [1,1],
            [2,1],
            [3,1],
            [4,1],
            [5,1],
            [6,1],
            [7,1]
        ];
    }
}
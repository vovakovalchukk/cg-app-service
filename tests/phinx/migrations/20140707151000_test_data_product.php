<?php

use Phinx\Migration\TestMigration;

class TestDataProduct extends TestMigration
{
    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->insertTestData('product', $this->getProductData());
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute('TRUNCATE table `product`');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function getProductData()
    {
        return [
            [1, 1, "sku1", "product1"],
            [2, 1, "sku2", "product2"],
            [3, 1, "sku3", "product3"],
            [4, 1, "sku4", "product4"],
            [5, 1, "sku5", "product5"],
            [6, 1, "sku6", "product6"]
        ];
    }
}
<?php

use Phinx\Migration\TestMigration;

class TestDataStock extends TestMigration
{
    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->insertTestData('stock', $this->getData());
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute('TRUNCATE table `stock`');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function getData()
    {
        return [
            [1, 1, "sku1"],
            [2, 1, "sku2"],
            [3, 1, "sku3"],
            [4, 1, "sku4"],
            [5, 1, "sku5"],
            [6, 1, "sku6"]
        ];
    }
}
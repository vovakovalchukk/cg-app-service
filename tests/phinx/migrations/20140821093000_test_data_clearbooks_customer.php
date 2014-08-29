<?php

use Phinx\Migration\TestMigration;

class TestDataClearBooksCustomer extends TestMigration
{
    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->insertTestData('clearbooksCustomer', $this->getData());
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute('TRUNCATE table `aliasMethod`');
        $this->execute('TRUNCATE table `alias`');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }


    protected function getData()
    {
        return [
            [1, 1, 1],
            [2, 1, 2],
            [3, 1, 3],
            [4, 1, 4],
            [5, 1, 5],
            [6, 1, 6]
        ];
    }
}
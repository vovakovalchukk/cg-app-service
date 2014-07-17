<?php

use Phinx\Migration\TestMigration;

class TestDataAlias extends TestMigration
{
    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->insertTestData('alias', $this->getAliasData());
        $this->insertTestData('aliasMethod', $this->getAliasMethodData());
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute('TRUNCATE table `aliasMethod`');
        $this->execute('TRUNCATE table `alias`');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }


    protected function getAliasData()
    {
        return [
            [1, 'alias1', 1],
            [2, 'alias2', 1],
            [3, 'alias3', 1],
            [4, 'alias4', 1],
            [5, 'alias5', 1],
            [6, 'alias6', 1],
        ];
    }

    protected function getAliasMethodData()
    {
        return [
            [1, 1],
            [1, 2],
            [1, 3],
            [2, 1],
            [2, 2],
            [3, 4],
            [3, 5],
            [3, 6],
            [4, 1],
            [4, 7],
            [5, 2],
            [5, 3],
            [6, 4],
            [6, 5],
            [6, 6]
        ];
    }
}
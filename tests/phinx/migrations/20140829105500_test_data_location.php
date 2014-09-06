<?php
use Phinx\Migration\TestMigration;

class TestDataLocation extends TestMigration
{
    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->insertTestData('location', $this->getNewLocationData());
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute('TRUNCATE table `location`');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function getNewLocationData()
    {
        return [
            [1, 2],
            [2, 2],
            [3, 3],
            [4, 2],
            [5, 3]
        ];
    }
}
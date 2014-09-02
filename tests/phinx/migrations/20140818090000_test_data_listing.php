<?php

use CG\Listing\Status;
use Phinx\Migration\TestMigration;

class TestDataListing extends TestMigration
{
    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->insertTestData('listing', $this->getListingData());
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute('TRUNCATE table `listing`');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function getListingData()
    {
        return [
            [1, 1, 1, '1', 'ebay', Status::ACTIVE, 1],
            [2, 1, 2, '2', 'ebay', Status::ACTIVE, 1],
            [3, 1, 3, '3', 'ebay', Status::INACTIVE, 1],
            [4, 1, 1, 'A', 'amazon', Status::ACTIVE, 1],
            [5, 1, 2, 'B', 'amazon', Status::INACTIVE, 1],
            [6, 1, 3, 'C', 'amazon', Status::ACTIVE, 1],
        ];
    }
}
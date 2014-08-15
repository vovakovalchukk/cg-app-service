<?php
use Phinx\Migration\TestMigration;

class TestDataUnimportedListing extends TestMigration
{
    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->insertTestData('unimportedListing', $this->getUnimportedListingData());
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute('TRUNCATE table `unimportedListing`');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function getUnimportedListingData()
    {
        return [
            [1, 1, 2, 'anExternalID', 'anSKU', 'aTitle', 'www.channelgrabber.com', 1, '2014-08-14 14:00:00', 'open', 1],
            [2, 1, 2, 'anExternalID2', 'anSKU2', 'aTitle2', 'www.reddit.com', 2, '2014-08-15 14:00:00', 'lost', 1],
            [3, 1, 2, 'anExternalID3', 'anSKU3', 'aTitle3', 'www.bbc.co.uk', 3, '2014-08-16 14:00:00', 'closed', 1],
            [4, 2, 2, 'anExternalID4', 'anSKU4', 'aTitle4', 'www.sky.com', 1, '2014-08-14 14:00:00', 'lost', 3],
            [5, 2, 3, 'anExternalID5', 'anSKU5', 'aTitle5', 'www.gamerscripts.com', 3, '2015-08-14 14:00:00', 'theFuture', 10]
        ];
    }
}
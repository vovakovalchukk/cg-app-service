<?php
use Phinx\Migration\AbstractMigration;

class ListingUniqueKey extends AbstractMigration
{
    public function up()
    {
        $orderTable = $this->table('listing');
        $orderTable
            ->addIndex(['accountId', 'externalId'], ['unique' => true])
            ->save();
    }

    public function down()
    {
        $orderTable = $this->table('listing');
        $orderTable
            ->removeIndex(['accountId', 'externalId'])
            ->save();
    }
}
<?php
use Phinx\Migration\AbstractMigration;

class UnimportedListingChannelField extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('unimportedListing');
        $table
            ->addColumn('channel', 'string')
            ->update();
    }
}

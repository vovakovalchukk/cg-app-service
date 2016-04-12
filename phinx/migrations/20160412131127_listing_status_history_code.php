<?php
use Phinx\Migration\AbstractMigration;

class ListingStatusHistoryCode extends AbstractMigration
{
    const TABLE_NAME = 'listingStatusHistory';

    public function change()
    {
        $this
            ->table(static::TABLE_NAME)
            ->addColumn('code', 'integer', ['null' => true])
            ->update();
    }
}

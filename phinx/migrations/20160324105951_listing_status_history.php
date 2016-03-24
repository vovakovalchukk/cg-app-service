<?php
use Phinx\Migration\AbstractMigration;

use Phinx\Db\Adapter\MysqlAdapter;

class ListingStatusHistory extends AbstractMigration
{
    const TABLE_NAME = 'listingStatusHistory';

    public function change()
    {
        $this
            ->table(static::TABLE_NAME)
            ->addColumn('listingId', 'integer', ['signed' => false])
            ->addColumn('timestamp', 'datetime')
            ->addColumn('status', 'string')
            ->addColumn('message', 'text', ['length' => MysqlAdapter::TEXT_LONG])
            ->addIndex('listingId')
            ->addIndex('status')
            ->create();
    }
}

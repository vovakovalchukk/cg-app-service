<?php
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;

class ListingStatusHistoryCode extends AbstractMigration
{
    const TABLE_NAME = 'listingStatusHistoryCode';

    public function change()
    {
        $this
            ->table(static::TABLE_NAME, ['id' => false, 'primary_key' => ['listingStatusHistoryId', 'code']])
            ->addColumn('listingStatusHistoryId', 'integer')
            ->addColumn('code', 'integer', ['null' => true])
            ->addForeignKey('listingStatusHistoryId', 'listingStatusHistory', 'id', ['delete' => ForeignKey::CASCADE, 'update' => ForeignKey::CASCADE])
            ->create();
    }
}

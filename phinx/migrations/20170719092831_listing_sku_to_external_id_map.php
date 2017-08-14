<?php
use Phinx\Migration\AbstractMigration;

class ListingSkuToExternalIdMap extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('listingSkuToExternalIdMap', ['id' => false, 'primary_key' => ['listingId', 'listingSku'], 'collation' => 'utf8_general_ci'])
            ->addColumn('listingId', 'integer')
            ->addColumn('listingSku', 'string')
            ->addColumn('externalId', 'string')
            ->create();
    }
}
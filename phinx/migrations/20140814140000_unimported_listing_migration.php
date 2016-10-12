<?php
use Phinx\Migration\AbstractMigration;

class UnimportedListingMigration extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('unimportedListing', ['collation' => 'utf8_general_ci']);
        $table
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('accountId', 'integer')
            ->addColumn('externalId', 'string')
            ->addColumn('sku', 'string')
            ->addColumn('title', 'string')
            ->addColumn('url', 'string')
            ->addColumn('imageId', 'integer')
            ->addColumn('createdDate', 'datetime')
            ->addColumn('status', 'string')
            ->addColumn('variationCount', 'integer')
            ->addIndex('organisationUnitId')
            ->addIndex('accountId')
            ->addIndex('imageId')
            ->create();
    }
}

<?php
use Phinx\Migration\AbstractMigration;

class ListingMigration extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('listing');
        $table
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('productId', 'integer')
            ->addColumn('externalId', 'string')
            ->addColumn('channel', 'string')
            ->addColumn('status', 'string')
            ->addForeignKey('productId', 'product', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex('organisationUnitId')
            ->addIndex('productId')
            ->create();
    }
}
 
<?php
use Phinx\Migration\AbstractMigration;

class StockMigration extends AbstractMigration
{
    public function change()
    {
        $stockTable = $this->table('stock');
        $stockTable
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('sku', 'string', ['null' => true])
            ->addIndex(['organisationUnitId', 'sku'])
            ->create();

        $locTable = $this->table('location');
        $locTable
            ->addColumn('organisationUnitId', 'integer')
            ->addIndex('organisationUnitId')
            ->create();

        $stockLocTable = $this->table('stockLocation', ['id' => false]);
        $stockLocTable
            ->addColumn("stockId", 'integer')
            ->addColumn("locationId", 'integer')
            ->addForeignKey('stockId', 'stock', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addForeignKey('locationId', 'location', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['stockId', 'locationId'])
            ->create();
    }
}
 
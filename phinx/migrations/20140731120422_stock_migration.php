<?php
use Phinx\Migration\AbstractMigration;

class StockMigration extends AbstractMigration
{
    public function change()
    {
        $stockTable = $this->table('stock', ['collation' => 'utf8_general_ci']);
        $stockTable
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('sku', 'string', ['null' => true])
            ->addIndex(['organisationUnitId', 'sku'])
            ->create();

        $locTable = $this->table('location', ['collation' => 'utf8_general_ci']);
        $locTable
            ->addColumn('organisationUnitId', 'integer')
            ->addIndex('organisationUnitId')
            ->create();

        $stockLocTable = $this->table('stockLocation', ['id' => false, 'collation' => 'utf8_general_ci']);
        $stockLocTable
            ->addColumn("stockId", 'integer')
            ->addColumn("locationId", 'integer')
            ->addColumn("onHand", 'integer', ['default' => 0])
            ->addColumn("allocated", 'integer', ['default' => 0])
            ->addForeignKey('stockId', 'stock', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addForeignKey('locationId', 'location', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['stockId', 'locationId'])
            ->create();
    }
}

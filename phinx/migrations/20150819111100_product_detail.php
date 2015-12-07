<?php
use Phinx\Migration\AbstractMigration;

class ProductDetail extends AbstractMigration
{
    public function change()
    {
        $this->table('productDetail')
            ->addColumn('organisationUnitId', 'integer', ['signed' => false])
            ->addColumn('sku', 'string')
            ->addColumn('weight', 'decimal', ['precision' => 12, 'scale' => 7, 'null' => true])
            ->addColumn('width', 'decimal', ['precision' => 12, 'scale' => 6, 'null' => true])
            ->addColumn('height', 'decimal', ['precision' => 12, 'scale' => 6, 'null' => true])
            ->addColumn('length', 'decimal', ['precision' => 12, 'scale' => 6, 'null' => true])
            ->addIndex(['organisationUnitId', 'sku'], ['unique' => true])
            ->create();
    }
}

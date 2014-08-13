<?php
use Phinx\Migration\AbstractMigration;

class ProductDeleted extends AbstractMigration
{
    public function change()
    {
        $product = $this->table('product');
        $product->addColumn('deleted', 'boolean')
            ->addIndex('deleted')
            ->update();
    }
}
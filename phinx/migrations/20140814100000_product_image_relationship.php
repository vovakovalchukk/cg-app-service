<?php
use Phinx\Migration\AbstractMigration;

class ProductImageRelationship extends AbstractMigration
{
    public function change()
    {
        $product = $this->table('productImage', ['id' => false, 'collation' => 'utf8_general_ci']);
        $product->addColumn('productId', 'integer')
            ->addColumn('imageId', 'integer')
            ->addColumn('order', 'integer')
            ->addIndex('productId')
            ->create();
    }
}

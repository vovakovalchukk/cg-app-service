<?php
use Phinx\Migration\AbstractMigration;

class ProductImageRelationship extends AbstractMigration
{
    public function change()
    {
        $product = $this->table('productImage', ['id' => false]);
        $product->addColumn('productId', 'integer')
            ->addColumn('imageId', 'integer')
            ->addColumn('order', 'integer')
            ->addIndex('productId')
            ->create();
    }
}
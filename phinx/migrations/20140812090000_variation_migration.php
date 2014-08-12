<?php
use Phinx\Migration\AbstractMigration;

class VariationMigration extends AbstractMigration
{
    public function change()
    {
        $product = $this->table('product');
        $product->addColumn('parentProductId', 'integer')
            ->update();

        $productAttribute = $this->table('productAttribute', ['id' => true, 'primary_key' => 'id']);
        $productAttribute->addColumn('productId', 'integer')
            ->addColumn('name', 'string')
            ->addForeignKey('productId', 'product', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->create();

        $productAttributeValue = $this->table('productAttributeValue', ['id' => false]);
        $productAttributeValue->addColumn('productAttributeId', 'integer')
            ->addForeignKey('productAttributeId', 'productAttribute', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addColumn('value', 'string')
            ->addColumn('productId', 'integer')
            ->addForeignKey('productId', 'product', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->create();
    }
}
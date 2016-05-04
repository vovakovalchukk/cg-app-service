<?php
use Phinx\Migration\AbstractMigration;

class ItemImage extends AbstractMigration
{
    public function change()
    {
        $product = $this->table('itemImage', ['id' => false]);
        $product->addColumn('itemId', 'string')
            ->addColumn('imageId', 'integer')
            ->addColumn('order', 'integer')
            ->addIndex('itemId')
            ->create();
    }
}

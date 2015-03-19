<?php
use Phinx\Migration\AbstractMigration;

class VariationMapping extends AbstractMigration
{
    public function change()
    {
        $productAttribute = $this->table('productAttributeMapping', ['id' => false, 'primary_key' => ['productId', 'accountId']]);
        $productAttribute
            ->addColumn('productId', 'integer')
            ->addColumn('accountId', 'integer')
            ->addColumn('name', 'string')
            ->addForeignKey('productId', 'product', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['productId', 'name'])
            ->create();
    }
}
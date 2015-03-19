<?php
use Phinx\Migration\AbstractMigration;

class VariationMapping extends AbstractMigration
{
    public function change()
    {
        $productAttribute = $this->table('productAttributeMapping');
        $productAttribute
            ->addColumn('productAttributeId', 'integer')
            ->addColumn('name', 'string')
            ->addColumn('accountId', 'integer')
            ->addForeignKey('productAttributeId', 'productAttribute', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['productAttributeId', 'accountId'], ['unique' => true])
            ->addIndex(['name'])
            ->create();
    }
}
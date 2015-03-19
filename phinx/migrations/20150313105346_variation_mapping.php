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
            ->addColumn('listingId', 'integer')
            ->addForeignKey('productAttributeId', 'productAttribute', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['productAttributeId', 'listingId'], ['unique' => true])
            ->addIndex(['name'])
            ->create();
    }
}
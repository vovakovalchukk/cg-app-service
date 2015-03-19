<?php
use Phinx\Migration\AbstractMigration;

class VariationMapping extends AbstractMigration
{
    public function change()
    {
        $productAttribute = $this->table('listingAttributeMapping', ['id' => false, 'primary_key' => ['productAttributeId', 'listingId']]);
        $productAttribute
            ->addColumn('productAttributeId', 'integer')
            ->addColumn('listingId', 'integer')
            ->addColumn('name', 'string')
            ->addForeignKey('productAttributeId', 'productAttribute', 'id',
                ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('listingId', 'listing', 'id',
                ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addIndex(['productAttributeId', 'name'])
            ->create();
    }
}
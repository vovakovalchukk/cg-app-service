<?php
use Phinx\Migration\AbstractMigration;

class VariationMapping extends AbstractMigration
{
    const LISTING_ATTRIBUTE_MAPPING_TABLE = 'listingAttributeMapping';
    const PRODUCT_ATTRIBUTE_TABLE = 'productAttribute';

    public function up()
    {
        $this->table(static::LISTING_ATTRIBUTE_MAPPING_TABLE)
            ->addColumn('productAttributeId', 'integer')
            ->addColumn('listingId', 'integer')
            ->addColumn('name', 'string')
            ->addForeignKey('productAttributeId', 'productAttribute', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addForeignKey('listingId', 'listing', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['productAttributeId', 'listingId'], ['unique' => true])
            ->create();

        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->table(static::PRODUCT_ATTRIBUTE_TABLE)
            ->removeIndex(['productId', 'name'])
            ->addIndex(['productId', 'name'], ['type' => 'unique'])
            ->update();

        $this->execute('ALTER TABLE ' . static::PRODUCT_ATTRIBUTE_TABLE . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->table(static::PRODUCT_ATTRIBUTE_TABLE)
            ->removeIndex(['productId', 'name'])
            ->addIndex(['productId', 'name'])
            ->update();
        $this->execute('SET FOREIGN_KEY_CHECKS=1');

        $this->execute('ALTER TABLE ' . static::PRODUCT_ATTRIBUTE_TABLE . ' CONVERT TO CHARACTER SET utf8');
        $this->dropTable(static::LISTING_ATTRIBUTE_MAPPING_TABLE);
    }
}
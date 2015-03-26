<?php
use Phinx\Migration\AbstractMigration;

class VariationMapping extends AbstractMigration
{
    const LISTING_ATTRIBUTE_MAPPING_TABLE = 'variationAttributeMap';
    const PRODUCT_ATTRIBUTE_TABLE = 'productAttribute';

    public function up()
    {
        $this->table(static::LISTING_ATTRIBUTE_MAPPING_TABLE)
            ->addColumn('productId', 'integer')
            ->addColumn('productAttributeId', 'integer')
            ->addColumn('listingId', 'integer')
            ->addColumn('name', 'string')
            ->addForeignKey('productAttributeId', 'productAttribute', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addForeignKey('productId', 'product', 'id',
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

        $this->execute('INSERT IGNORE INTO variationAttributeMap (productId, productAttributeId, listingId, name)
                        SELECT p.id as productId, pa.id as productAttributeId, plm.listingId, pa.name
                        FROM product p
                        INNER JOIN productToListingMap plm ON p.id = plm.productId
                        INNER JOIN listing l ON plm.listingId = l.id
                        INNER JOIN productAttribute pa ON p.id = pa.productId
                        WHERE p.parentProductId = 0');
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
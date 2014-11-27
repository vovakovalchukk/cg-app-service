<?php
use Phinx\Migration\AbstractMigration;

class OrderItemVariations extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('item')->removeColumn('itemVariationAttribute')->update();
        $this->table('itemVariationAttribute', ['id' => false, 'primary_key' => ['itemId', 'name']])
            ->addColumn('itemId', 'string')
            ->addColumn('name', 'string')
            ->addColumn('value', 'string')
            ->addForeignKey('itemId', 'item', 'id')
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('itemVariationAttribute')->drop();
        $this->table('item')
            ->addColumn('itemVariationAttribute', 'string', ['after' => 'individualItemDiscountPrice'])
            ->update();
    }
}

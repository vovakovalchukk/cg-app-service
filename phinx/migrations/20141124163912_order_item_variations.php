<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderItemVariations extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('item')->removeColumn('itemVariationAttribute')->update();
        $this->table('itemVariationAttribute', ['id' => false, 'primary_key' => ['itemId', 'name'], 'collation' => 'utf8_general_ci'])
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

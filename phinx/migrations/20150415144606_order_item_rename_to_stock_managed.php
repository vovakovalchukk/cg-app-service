<?php
use CG\Order\Shared\Item\Entity as Item;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderItemRenameToStockManaged extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_NAME = 'item';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->table(static::TABLE_NAME)
            ->removeColumn('isStockManaged')
            ->addColumn('stockManaged', 'boolean', ['default' => Item::DEFAULT_IS_STOCK_MANAGED])
            ->update();
    }

    public function down()
    {
        $this->table(static::TABLE_NAME)
            ->removeColumn('stockManaged')
            ->addColumn('isStockManaged', 'boolean', ['default' => Item::DEFAULT_IS_STOCK_MANAGED])
            ->update();
    }
}

<?php
use CG\Order\Shared\Item\Entity as Item;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderItemIsStockManaged extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_NAME = 'item';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table(static::TABLE_NAME)
            ->addColumn('isStockManaged', 'boolean', ['default' => Item::DEFAULT_IS_STOCK_MANAGED])
            ->update();
    }
}

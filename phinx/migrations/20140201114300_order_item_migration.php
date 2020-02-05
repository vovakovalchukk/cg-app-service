<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderItemMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $item = $this->table('item', ['id' => false, 'primary_key' => 'id', 'collation' => 'utf8_general_ci']);
        $item->addColumn('id', 'string')
            ->addColumn('orderId', 'string')
            ->create();
    }
}

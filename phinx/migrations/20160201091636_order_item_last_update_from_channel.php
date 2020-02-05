<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderItemLastUpdateFromChannel extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('item')
            ->addColumn('lastUpdateFromChannel', 'datetime', ['null' => true])
            ->update();
    }
}

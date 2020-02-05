<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderLastUpdateFromChannel extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('order')
            ->addColumn('lastUpdateFromChannel', 'datetime', ['null' => true])
            ->update();

        $this->table('orderLive')
            ->addColumn('lastUpdateFromChannel', 'datetime', ['null' => true])
            ->update();
    }
}

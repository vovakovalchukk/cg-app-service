<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderExternalUsername extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        foreach (['order', 'orderLive'] as $table) {
            $this
                ->table($table)
                ->addColumn('externalUsername', 'string', ['null' => true])
                ->update();
        }
    }
}

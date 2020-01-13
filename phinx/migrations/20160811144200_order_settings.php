<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderSettings extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('orderSettings', ['id' => false, 'primary_key' => 'id', 'collation' => 'utf8_general_ci'])
            ->addColumn('id', 'integer')
            ->addColumn('autoArchiveTimeframe', 'string')
            ->create();
    }
}

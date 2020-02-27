<?php
use Phinx\Db\Adapter\MysqlAdapter as Adapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class CreateUserPreferenceTable extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('userPreference', ['collation' => 'utf8_general_ci'])
            ->addColumn('preference', 'string', ['limit' => Adapter::TEXT_LONG])
            ->addColumn('mongoId', 'string')
            ->addIndex(['mongoId'], ['unique' => true])
            ->create();
    }
}
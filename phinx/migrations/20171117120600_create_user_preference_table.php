<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter as Adapter;

class CreateUserPreferenceTable extends AbstractMigration
{
    public function change()
    {
        $this->table('userPreference', ['collation' => 'utf8_general_ci'])
            ->addColumn('preference', 'string', ['limit' => Adapter::TEXT_LONG])
            ->addColumn('mongoId', 'string')
            ->addIndex(['mongoId'], ['unique' => true])
            ->create();
    }
}
<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter as Adapter;

class RemoveUniqueConstraintUserPreferenceTable extends AbstractMigration
{
    public function change()
    {
        $this->table('userPreference', ['collation' => 'utf8_general_ci'])
            ->removeIndex(['mongoId'], ['unique' => true])
            ->changeColumn('mongoId', 'string', ['null' => true])
            ->update();
    }
}
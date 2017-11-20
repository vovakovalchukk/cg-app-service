<?php
use Phinx\Migration\AbstractMigration;

class CreateUserPreferenceTable extends AbstractMigration
{
    public function change()
    {
        $this->table('userPreference', ['collation' => 'utf8_general_ci'])
            ->addColumn('preference', 'string')
            ->addColumn('mongoId', 'string')
            ->addIndex(['mongoId'], ['unique' => true])
            ->create();
    }
}
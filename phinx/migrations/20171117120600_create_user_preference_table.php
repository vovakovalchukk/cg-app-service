<?php
use Phinx\Migration\AbstractMigration;

class CreateUserPreferenceTable extends AbstractMigration
{
    public function change()
    {
        $this->table('userPreference')
            ->addColumn('preference', 'string')
            ->addColumn('mongoId', 'string')
            ->addIndex(['mongoId'], ['unique' => true])
            ->create();
    }
}
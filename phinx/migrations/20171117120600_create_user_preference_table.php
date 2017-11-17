<?php
use Phinx\Migration\AbstractMigration;

class CreateUserPreferenceTable extends AbstractMigration
{
    public function change()
    {
        $this->table('userPreference')
            ->addColumn('preference', 'string')
            ->create();
    }
}
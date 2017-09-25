<?php

use Phinx\Migration\AbstractMigration;

class EkmRegistration extends AbstractMigration
{
    public function change()
    {
        $table = $this->table("ekmRegistration", ['id' => true, 'collation' => 'utf8_general_ci']);
        $table->addColumn('ekmUsername', 'string')
            ->addColumn('json', 'string')
            ->addColumn('referrer', 'string')
            ->addColumn('application', 'string')
            ->addColumn('token', 'string')
            ->addColumn('rootOrganisationUnitId', 'integer')
            ->addColumn('createdDate', 'datetime')
            ->addColumn('completedDate', 'datetime')
            ->create();
    }
}
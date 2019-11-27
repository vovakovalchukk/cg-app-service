<?php

use Phinx\Migration\AbstractMigration;

class AddSuppliersTable extends AbstractMigration
{
    public function change()
    {
        $this->table('supplier')
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('name', 'string')
            ->addIndex(['organisationUnitId', 'name'], ['unique' => true])
            ->create();
    }
}

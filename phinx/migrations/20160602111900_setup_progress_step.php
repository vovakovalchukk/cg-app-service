<?php
use Phinx\Migration\AbstractMigration;

class SetupProgressStep extends AbstractMigration
{
    public function change()
    {
        $product = $this->table('setupProgressStep', ['id' => false, 'collation' => 'utf8_general_ci']);
        $product->addColumn('organisationUnitId', 'integer')
            ->addColumn('name', 'string')
            ->addColumn('status', 'string')
            ->addColumn('modified', 'datetime')
            ->addIndex(['organisationUnitId', 'name'], ['unique' => true])
            ->create();
    }
}

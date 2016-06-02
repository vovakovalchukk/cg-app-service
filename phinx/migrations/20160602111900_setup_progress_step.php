<?php
use Phinx\Migration\AbstractMigration;

class SetupProgressStep extends AbstractMigration
{
    public function change()
    {
        $product = $this->table('setupProgressStep', ['id' => false]);
        $product->addColumn('organisationUnitId', 'integer')
            ->addColumn('name', 'string')
            ->addColumn('status', 'string')
            ->addColumn('modified', 'datetime')
            ->addIndex('organisationUnitId')
            ->create();
    }
}

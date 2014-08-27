<?php
use Phinx\Migration\AbstractMigration;

class SettingsClearbooksCustomerMigration extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('clearbooksCustomer');
        $table
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('clearbooksCustomerId', 'integer')
            ->create();
    }
}
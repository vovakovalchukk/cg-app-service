<?php
use Phinx\Migration\AbstractMigration;

class InvoiceMapToCompanySite extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('invoiceMapping', ['id' => false, 'primary_key' => ['accountId', 'site'], 'collation' => 'utf8_general_ci'])
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('accountId', 'integer')
            ->addColumn('site', 'string')
            ->addColumn('invoiceId', 'string', ['null' => true])
            ->addColumn('sendViaEmail', 'boolean', ['null' => true])
            ->addColumn('sendToFba', 'boolean', ['null' => true])
            ->create();
    }
}

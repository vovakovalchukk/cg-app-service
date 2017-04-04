<?php

use Phinx\Migration\AbstractMigration;

class InvoiceMapToCompanySite extends AbstractMigration
{
    public function up()
    {
        $table = $this->table(
            'invoiceMapping',
            [
                'collation' => 'utf8_general_ci'
            ]
        );

        $table->addColumn('organisationUnitId', 'integer')
            ->addColumn('accountId', 'integer')
            ->addColumn('site', 'string')
            ->addColumn('invoiceId', 'integer')
            ->addColumn('sendViaEmail', 'string')
            ->addColumn('sendToFba', 'string')
            ->addIndex([
                'site', 'accountId'
            ])->create();
    }

    public function down()
    {
        $this->table('invoiceMapping')->drop();
    }
}

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
            ->addColumn('invoiceId', 'string', ['null' => true])
            ->addColumn('sendViaEmail', 'string', ['null' => true])
            ->addColumn('sendToFba', 'string', ['null' => true])
            ->addIndex([
                'accountId', 'site'
            ])->create();
    }

    public function down()
    {
        $this->table('invoiceMapping')->drop();
    }
}

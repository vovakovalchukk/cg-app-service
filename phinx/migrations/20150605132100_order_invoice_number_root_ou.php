<?php
use Phinx\Migration\AbstractMigration;

class OrderInvoiceNumberRootOu extends AbstractMigration
{
    public function change()
    {
        $this->table('order')
            ->addColumn('invoiceNumber', 'integer', ['null' => true])
            ->addColumn('rootOrganisationUnitId', 'integer', ['null' => true])
            ->addIndex(['rootOrganisationUnitId', 'invoiceNumber'], ['unique' => true])
            ->update();

        $this->table('orderLive')
            ->addColumn('invoiceNumber', 'integer', ['null' => true])
            ->addColumn('rootOrganisationUnitId', 'integer', ['null' => true])
            ->addIndex(['rootOrganisationUnitId', 'invoiceNumber'], ['unique' => true])
            ->update();
    }
}

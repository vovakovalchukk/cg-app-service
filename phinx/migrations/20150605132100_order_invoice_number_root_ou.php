<?php
use Phinx\Migration\AbstractMigration;

class OrderInvoiceNumberRootOu extends AbstractMigration
{
    public function change()
    {
        $this->table('order')
            ->addColumn('invoiceNumber', 'integer', ['null' => true])
            ->addColumn('rootOrganisationUnitId', 'integer', ['null' => true])
            ->addIndex(['invoiceNumber', 'rootOrganisationUnitId'], ['unique' => true])
            ->update();

        $this->table('orderLive')
            ->addColumn('invoiceNumber', 'integer', ['null' => true])
            ->addColumn('rootOrganisationUnitId', 'integer', ['null' => true])
            ->addIndex(['invoiceNumber', 'rootOrganisationUnitId'], ['unique' => true])
            ->update();
    }
}

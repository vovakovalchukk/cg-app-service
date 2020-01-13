<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderInvoiceNumberRootOu extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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

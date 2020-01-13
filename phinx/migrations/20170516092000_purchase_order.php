<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\EnvironmentAwareInterface;

class PurchaseOrder extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $table = $this->table('purchaseOrder');

        $table->addColumn('organisationUnitId', 'integer')
            ->addColumn('userId', 'integer')
            ->addColumn('status', 'string')
            ->addColumn('externalId', 'string')
            ->addColumn('created', 'datetime')
            ->addIndex('organisationUnitId')
            ->create();

        $table = $this->table('purchaseOrderItem');

        $table->addColumn('organisationUnitId', 'integer')
            ->addColumn('purchaseOrderId', 'integer')
            ->addColumn('sku', 'string')
            ->addColumn('quantity', 'integer')
            ->addIndex('organisationUnitId')
            ->addForeignKey('purchaseOrderId', 'purchaseOrder', 'id', ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE])
            ->create();
    }
}

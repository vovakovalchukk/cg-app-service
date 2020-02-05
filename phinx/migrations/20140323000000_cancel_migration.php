<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class CancelMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $cancel = $this->table('cancel', ['id' => true, 'primary_key' => 'id', 'collation' => 'utf8_general_ci']);
        $cancel->addColumn('reason', 'string')
            ->addColumn('orderId', 'string')
            ->addColumn('type', 'string')
            ->addColumn('shippingAmount', 'decimal', ['precision' => 12, 'scale' => 4])
            ->addColumn('timestamp', 'timestamp')
            ->create();

        $cancelItem = $this->table('cancelItem', ['id' => false, 'collation' => 'utf8_general_ci']);
        $cancelItem->addColumn('cancelId', 'integer')
            ->addColumn('orderItemId', 'string')
            ->addColumn('sku', 'string')
            ->addColumn('quantity', 'integer')
            ->addColumn('amount', 'decimal', ['precision' => 12, 'scale' => 4])
            ->addColumn('unitPrice', 'decimal', ['precision' => 12, 'scale' => 4]);
        $cancelItem->create();
    }
}

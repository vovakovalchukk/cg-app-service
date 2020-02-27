<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderItemRefund extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('orderItemRefund', ['row_format' => 'COMPRESSED'])
            ->addColumn('organisationUnitId', 'integer', ['null' => false])
            ->addColumn('itemId', 'string', ['null' => false])
            ->addColumn('amount', 'decimal', ['precision' => 12, 'scale' => 4, 'null' => false])
            ->addIndex(['organisationUnitId', 'itemId'])
            ->addIndex('itemId')
            ->create();
    }
}
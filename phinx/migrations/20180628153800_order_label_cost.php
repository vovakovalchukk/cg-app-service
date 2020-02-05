<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderLabelCost extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('orderLabel')
            ->addColumn('costPrice', 'decimal', ['precision' => 12, 'scale' => 4, 'null' => true])
            ->addColumn('costCurrencyCode', 'string', ['null' => true])
            ->update();
    }
}
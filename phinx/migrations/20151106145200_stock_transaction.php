<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockTransaction extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('stockTransaction', ['id' => false, 'primary_key' => ['id'], 'collation' => 'utf8_general_ci'])
            ->addColumn('id', 'string')
            ->addColumn('appliedDate', 'datetime')
            ->create();
    }
}

<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductSettings extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('productSettings', ['id' => false, 'primary_key' => ['id'], 'collation' => 'utf8_general_ci'])
            ->addColumn('id', 'integer', ['signed' => false])
            ->addColumn('defaultStockMode', 'string', ['length' => 10, 'null' => true])
            ->addColumn('defaultStockLevel', 'integer', ['null' => true])
            ->create();
    }
}

<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductStockLevels extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('product')
            ->addColumn('stockMode', 'string', ['length' => 10, 'null' => true])
            ->addColumn('stockLevel', 'integer', ['null' => true])
            ->update();
    }
}

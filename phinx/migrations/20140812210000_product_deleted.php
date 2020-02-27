<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductDeleted extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $product = $this->table('product');
        $product->addColumn('deleted', 'boolean')
            ->addIndex('deleted')
            ->update();
    }
}
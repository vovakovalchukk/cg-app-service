<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductImageRelationship extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $product = $this->table('productImage', ['id' => false, 'collation' => 'utf8_general_ci']);
        $product->addColumn('productId', 'integer')
            ->addColumn('imageId', 'integer')
            ->addColumn('order', 'integer')
            ->addIndex('productId')
            ->create();
    }
}

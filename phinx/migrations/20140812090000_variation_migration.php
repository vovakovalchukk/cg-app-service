<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class VariationMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $product = $this->table('product');
        $product->addColumn('parentProductId', 'integer')
            ->addIndex('parentProductId')
            ->update();

        $productAttribute = $this->table('productAttribute', ['id' => true, 'primary_key' => 'id', 'collation' => 'utf8_general_ci']);
        $productAttribute->addColumn('productId', 'integer')
            ->addColumn('name', 'string')
            ->addForeignKey('productId', 'product', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['productId', 'name'])
            ->create();

        $productAttributeValue = $this->table('productAttributeValue', ['id' => false, 'collation' => 'utf8_general_ci']);
        $productAttributeValue->addColumn('productAttributeId', 'integer')
            ->addForeignKey('productAttributeId', 'productAttribute', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addColumn('value', 'string')
            ->addColumn('productId', 'integer')
            ->addForeignKey('productId', 'product', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['productId', 'productAttributeId'])
            ->create();
    }
}

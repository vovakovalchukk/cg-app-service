<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class VariationMapping extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $productAttribute = $this->table('variationAttributeMap', ['collation' => 'utf8_general_ci']);
        $productAttribute
            ->addColumn('productId', 'integer')
            ->addColumn('productAttributeId', 'integer')
            ->addColumn('name', 'string')
            ->addColumn('listingId', 'integer')
            ->addForeignKey('productAttributeId', 'productAttribute', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addForeignKey('productId', 'product', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addForeignKey('listingId', 'listing', 'id',
                ['delete' => 'NOACTION', 'update' => 'NOACTION'])
            ->addIndex(['productAttributeId', 'listingId'], ['unique' => true])
            ->addIndex(['name'])
            ->create();
    }
}

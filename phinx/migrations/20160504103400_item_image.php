<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ItemImage extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $product = $this->table('itemImage', ['id' => false, 'collation' => 'utf8_general_ci']);
        $product->addColumn('itemId', 'string')
            ->addColumn('imageId', 'integer')
            ->addColumn('order', 'integer')
            ->addIndex('itemId')
            ->create();
    }
}

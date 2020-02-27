<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductToListingMapSkus extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function change()
    {
        $this->table('productToListingMap')
            ->addColumn('productSku', 'string')
            ->update();
    }
}

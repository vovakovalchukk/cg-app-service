<?php
use Phinx\Migration\AbstractMigration;

class ProductToListingMapSkus extends AbstractMigration
{
    public function change()
    {
        $this->table('productToListingMap')
            ->addColumn('productSku', 'string')
            ->update();
    }
}

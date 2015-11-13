<?php
use Phinx\Migration\AbstractMigration;

class ProductOuSkuKey extends AbstractMigration
{
    public function change()
    {
        $this->table('product')
            ->addIndex(['organisationUnitId', 'sku'])
            ->update();
    }
}

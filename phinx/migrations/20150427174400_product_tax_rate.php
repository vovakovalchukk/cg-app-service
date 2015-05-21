<?php
use Phinx\Migration\AbstractMigration;

class ProductTaxRate extends AbstractMigration
{
    public function change()
    {
        $this->table('product')
             ->addColumn('taxRateId', 'string', ['null' => true])
             ->update();
    }
}

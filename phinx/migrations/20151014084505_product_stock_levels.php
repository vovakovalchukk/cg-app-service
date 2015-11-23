<?php
use Phinx\Migration\AbstractMigration;

class ProductStockLevels extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('product')
            ->addColumn('stockMode', 'string', ['length' => 10, 'null' => true])
            ->addColumn('stockLevel', 'integer', ['null' => true])
            ->update();
    }
}

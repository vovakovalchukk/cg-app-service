<?php
use Phinx\Migration\AbstractMigration;

class ProductSettings extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('productSettings', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['signed' => false])
            ->addColumn('defaultStockMode', 'string', ['length' => 10, 'null' => true])
            ->addColumn('defaultStockLevel', 'integer', ['null' => true])
            ->create();
    }
}
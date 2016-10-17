<?php
use Phinx\Migration\AbstractMigration;

class ProductMigration extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('product', ['collation' => 'utf8_general_ci']);
        $table->addColumn('organisationUnitId', 'integer')
            ->addColumn('sku', 'string', ['null' => true])
            ->addColumn('name', 'string')
            ->create();
    }
}

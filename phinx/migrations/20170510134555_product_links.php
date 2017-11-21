<?php

use Phinx\Migration\AbstractMigration;

class ProductLinks extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('productLink', ['id' => false, 'primary_key' => ['organisationUnitId', 'productSku', 'stockSku'], 'collation' => 'utf8_general_ci'])
            ->addColumn('organisationUnitId', 'integer', ['null' => false])
            ->addColumn('productSku', 'string', ['null' => false])
            ->addColumn('stockSku', 'string', ['null' => false])
            ->addColumn('quantity', 'integer', ['null' => false, 'signed' => false])
            ->create();
    }
}
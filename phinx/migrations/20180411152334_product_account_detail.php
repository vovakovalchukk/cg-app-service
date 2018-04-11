<?php
use Phinx\Migration\AbstractMigration;

class ProductAccountDetail extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('productAccountDetail', ['id' => false, 'primary_key' => ['productId', 'accountId']])
            ->addColumn('productId', 'integer')
            ->addColumn('accountId', 'integer')
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('price', 'decimal', ['precision' => 12, 'scale' => 4, 'null' => true])
            ->create();
    }
}
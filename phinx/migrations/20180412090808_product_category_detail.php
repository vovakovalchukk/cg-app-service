<?php
use Phinx\Migration\AbstractMigration;

class ProductCategoryDetail extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('productCategoryDetail', ['id' => false, 'primary_key' => ['productId', 'categoryId']])
            ->addColumn('productId', 'integer')
            ->addColumn('categoryId', 'integer')
            ->addColumn('channel', 'string')
            ->addColumn('organisationUnitId', 'integer')
            ->create();
    }
}
<?php

use Phinx\Migration\AbstractMigration;

class AddReturnsPolicyToProductCategoryEbayDetail extends AbstractMigration
{
    public function change()
    {
        $this->table('productCategoryEbayDetail')
            ->addColumn('returnPolicy', 'integer', ['null' => true])
            ->save();
    }
}
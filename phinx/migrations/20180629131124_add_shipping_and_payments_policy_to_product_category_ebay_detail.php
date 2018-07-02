<?php

use Phinx\Migration\AbstractMigration;

class AddShippingAndPaymentsPolicyToProductCategoryEbayDetail extends AbstractMigration
{
    public function change()
    {
        $this->table('productCategoryEbayDetail')
            ->addColumn('shippingPolicy', 'integer', ['null' => true])
            ->addColumn('paymentPolicy', 'integer', ['null' => true])
            ->save();
    }
}
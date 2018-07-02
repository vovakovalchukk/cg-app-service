<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddShippingAndPaymentsPolicyToProductCategoryEbayDetail extends AbstractMigration
{
    public function change()
    {
        $this->table('productCategoryEbayDetail')
            ->addColumn('shippingPolicy', 'string', ['limit' => MysqlAdapter::TEXT_TINY, 'null' => true])
            ->addColumn('paymentPolicy', 'string', ['limit' => MysqlAdapter::TEXT_TINY, 'null' => true])
            ->save();
    }
}
<?php
use Phinx\Migration\AbstractMigration;

class OrderShippingOriginCountry extends AbstractMigration
{
    public function change()
    {
        $this->table('order')
            ->addColumn('shippingOriginCountryCode', 'string', ['null' => true, 'limit' => 2])
            ->update();

        $this->table('orderLive')
            ->addColumn('shippingOriginCountryCode', 'string', ['null' => true, 'limit' => 2])
            ->update();
    }
}
<?php
use Phinx\Migration\AbstractMigration;

class OrderItemExternalListingId extends AbstractMigration
{
    public function change()
    {
        $this->table('orderItem')
            ->addColumn('externalListingId', 'string', ['null' => true])
            ->addIndex(['externalListingId'], ['unique' => true])
            ->update();
    }
}
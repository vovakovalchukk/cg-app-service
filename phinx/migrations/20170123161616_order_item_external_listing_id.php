<?php
use Phinx\Migration\AbstractMigration;

class OrderItemExternalListingId extends AbstractMigration
{
    public function change()
    {
        $this->table('item')
            ->addColumn('externalListingId', 'string', ['null' => true])
            ->addIndex(['externalListingId'], ['unique' => true])
            ->update();
    }
}
<?php
use Phinx\Migration\AbstractMigration;

class OrderItemLastUpdateFromChannel extends AbstractMigration
{
    public function change()
    {
        $this->table('item')
            ->addColumn('lastUpdateFromChannel', 'datetime', ['null' => true])
            ->update();
    }
}

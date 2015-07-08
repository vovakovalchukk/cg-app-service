<?php
use Phinx\Migration\AbstractMigration;

class OrderLastUpdateFromChannel extends AbstractMigration
{
    public function change()
    {
        $this->table('order')
            ->addColumn('lastUpdateFromChannel', 'datetime', ['null' => true])
            ->update();

        $this->table('orderLive')
            ->addColumn('lastUpdateFromChannel', 'datetime', ['null' => true])
            ->update();
    }
}

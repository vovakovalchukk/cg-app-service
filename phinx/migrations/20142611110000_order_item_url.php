<?php

use Phinx\Migration\AbstractMigration;

class OrderItemUrl extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('item');
        $table->addColumn('url', 'string', ['length' => 2000, 'after' => 'status', 'null' => true])
              ->update();
    }
}
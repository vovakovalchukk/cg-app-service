<?php

use Phinx\Migration\AbstractMigration;

class CancelItemOrderIs extends AbstractMigration
{
    public function change()
    {
        $cancelItem = $this->table('cancelItem');
        $cancelItem->addColumn('orderId', 'string');
        $cancelItem->update();
    }

}
<?php
use Phinx\Migration\AbstractMigration;

class OrderExternalUsername extends AbstractMigration
{
    public function change()
    {
        foreach (['order', 'orderLive'] as $table) {
            $this
                ->table($table)
                ->addColumn('externalUsername', 'string', ['null' => true])
                ->update();
        }
    }
}

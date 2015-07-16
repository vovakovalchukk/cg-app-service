<?php
use Phinx\Migration\AbstractMigration;

class OrderInvoiceEmail extends AbstractMigration
{
    public function change()
    {
        foreach (['order', 'orderLive'] as $table) {
            $this
                ->table($table)
                ->addColumn('emailDate', 'datetime', ['null' => true])
                ->update();
        }
    }
}

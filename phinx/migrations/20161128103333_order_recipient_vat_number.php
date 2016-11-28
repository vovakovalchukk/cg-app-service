<?php
use Phinx\Migration\AbstractMigration;

class OrderRecipientVatNumber extends AbstractMigration
{
    public function change()
    {
        $this->table('order')
            ->addColumn('recipientVatNumber', 'string', ['null' => true])
            ->update();

        $this->table('orderLive')
            ->addColumn('recipientVatNumber', 'string', ['null' => true])
            ->update();
    }
}
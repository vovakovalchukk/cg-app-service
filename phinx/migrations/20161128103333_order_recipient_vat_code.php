<?php
use Phinx\Migration\AbstractMigration;

class OrderRecipientVatCode extends AbstractMigration
{
    public function change()
    {
        $this->table('order')
            ->addColumn('recipientVatCode', 'string', ['null' => true])
            ->update();

        $this->table('orderLive')
            ->addColumn('recipientVatCode', 'string', ['null' => true])
            ->update();
    }
}
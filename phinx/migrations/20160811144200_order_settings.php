<?php
use Phinx\Migration\AbstractMigration;

class OrderSettings extends AbstractMigration
{
    public function change()
    {
        $this->table('orderSettings', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'integer')
            ->addColumn('autoArchiveTimeframe', 'string')
            ->create();
    }
}

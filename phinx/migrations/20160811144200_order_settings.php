<?php
use Phinx\Migration\AbstractMigration;

class OrderSettings extends AbstractMigration
{
    public function change()
    {
        $this->table('orderSettings', ['id' => false, 'primary_key' => 'id', 'collation' => 'utf8_general_ci'])
            ->addColumn('id', 'integer')
            ->addColumn('autoArchiveTimeframe', 'string')
            ->create();
    }
}

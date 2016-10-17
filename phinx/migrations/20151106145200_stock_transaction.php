<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class StockTransaction extends AbstractMigration
{
    public function change()
    {
        $this->table('stockTransaction', ['id' => false, 'primary_key' => ['id'], 'collation' => 'utf8_general_ci'])
            ->addColumn('id', 'string')
            ->addColumn('appliedDate', 'datetime')
            ->create();
    }
}

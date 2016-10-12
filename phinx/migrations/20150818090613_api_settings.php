<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class ApiSettings extends AbstractMigration
{
    public function change()
    {
        $this->table('api', ['id' => false, 'primary_key' => ['id'], 'collation' => 'utf8_general_ci'])
            ->addColumn('id', 'integer', ['signed' => false])
            ->addColumn('orderNotificationUrl', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->addColumn('stockNotificationUrl', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->create();
    }
}

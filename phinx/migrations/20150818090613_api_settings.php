<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ApiSettings extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('api', ['id' => false, 'primary_key' => ['id'], 'collation' => 'utf8_general_ci'])
            ->addColumn('id', 'integer', ['signed' => false])
            ->addColumn('orderNotificationUrl', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->addColumn('stockNotificationUrl', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->create();
    }
}

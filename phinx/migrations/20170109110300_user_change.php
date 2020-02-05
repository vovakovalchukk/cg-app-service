<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class UserChange extends AbstractMigration implements EnvironmentAwareInterface
{
    const CILEX_LOCATION = '/../../console/app.php';
    const CILEX_CMD = 'phinx:migrateMongoUserChangeDataToMysql';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->table('userChange', ['id' => false, 'primary_key' => 'orderId'])
            ->addColumn('orderId', 'string', ['limit' => 120])
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('changes', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->create();

        $this->insertMongoData();
    }
    
    protected function insertMongoData()
    {
        if (file_exists(__DIR__ . static::CILEX_LOCATION)) {
            echo shell_exec('php ' . __DIR__ . static::CILEX_LOCATION . ' ' . static::CILEX_CMD);
        }
    }

    public function down()
    {
        $this->table('userChange')->drop();
    }
}
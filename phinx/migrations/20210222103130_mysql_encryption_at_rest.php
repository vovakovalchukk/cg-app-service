<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class MysqlEncryptionAtRest extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange('address', 'ENCRYPTION="Y"');
    }

    public function down()
    {
        $this->onlineSchemaChange('address', 'ENCRYPTION="N"');
    }
}

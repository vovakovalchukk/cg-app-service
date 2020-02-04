<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ChangeTrackingColumnsToNull extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange('tracking', 'MODIFY `number` VARCHAR(120) DEFAULT NULL');
        $this->onlineSchemaChange('tracking', 'MODIFY `carrier` VARCHAR(120) DEFAULT NULL');
    }

    public function down()
    {
        $this->onlineSchemaChange('tracking', 'MODIFY `number` VARCHAR(120) DEFAULT NOT NULL');
        $this->onlineSchemaChange('tracking', 'MODIFY `carrier` VARCHAR(120) DEFAULT NOT NULL');
    }
}

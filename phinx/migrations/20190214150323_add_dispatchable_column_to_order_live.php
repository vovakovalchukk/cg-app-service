<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class AddDispatchableColumnToOrderLive extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $alter = 'ADD COLUMN dispatchable TINYINT(1) NOT NULL DEFAULT 0';
        $this->onlineSchemaChange('orderLive', $alter);
    }

    public function down()
    {
        $alter = 'DROP COLUMN dispatchable';
        $this->onlineSchemaChange('orderLive', $alter);
    }
}
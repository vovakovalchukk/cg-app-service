<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderItemCustomisation extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    const TABLE = 'item';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange(static::TABLE, 'ADD COLUMN customisation MEDIUMTEXT');
    }

    public function down()
    {
        $this->onlineSchemaChange(static::TABLE, 'DROP COLUMN customisation');
    }
}
<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class CategoryAccountId extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $alter = 'ADD COLUMN `accountId` int(11) UNSIGNED NULL, '
            . 'ADD INDEX `accountId` (`accountId`), '
            . 'ADD UNIQUE INDEX `ExternalIdAccountId` (`externalId`, `accountId`)';

        $this->onlineSchemaChange('category', $alter);
    }

    public function down()
    {
        $alter = 'DROP INDEX `ExternalIdAccountId`, '
            . 'DROP INDEX `accountId`, '
            . 'DROP COLUMN `accountId`';

        $this->onlineSchemaChange('category', $alter);
    }
}
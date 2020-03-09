<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class CategoryIndexIncludeParent extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    private const TABLE = 'category';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $alter = 'DROP INDEX `ExternalIdChannelMarketplaceVersion`, ADD CONSTRAINT `ExternalIdParentIdChannelMarketplaceVersion` UNIQUE KEY (`externalId`, `parentId`, `channel`, `marketplace`, `version`)';
        $this->onlineSchemaChange(static::TABLE, $alter);
    }

    public function down()
    {
        $alter = 'DROP INDEX `ExternalIdParentIdChannelMarketplaceVersion`, ADD CONSTRAINT `ExternalIdChannelMarketplaceVersion` UNIQUE KEY (`externalId`, `channel`, `marketplace`, `version`)';
        $this->onlineSchemaChange(static::TABLE, $alter);
    }

    protected function getAdditionalArguments(): array
    {
        return ['--nocheck-unique-key-change'];
    }
}

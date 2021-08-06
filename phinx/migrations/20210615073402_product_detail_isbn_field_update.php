<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductDetailIsbnFieldUpdate extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    const TABLE = 'productDetail';

    public function supportsEnvironment($environment): bool
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange(static::TABLE, 'MODIFY COLUMN `isbn` VARCHAR(17) NULL');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange(static::TABLE, 'MODIFY COLUMN `isbn` VARCHAR(13) NULL');
    }
}

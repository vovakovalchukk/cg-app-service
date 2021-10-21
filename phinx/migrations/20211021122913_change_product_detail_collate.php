<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ChangeProductDetailCollate extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange('productDetail', 'MODIFY description MEDIUMTEXT COLLATE utf8mb4_0900_ai_ci');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange('productDetail', 'MODIFY description MEDIUMTEXT COLLATE utf8_general_ci');
    }
}

<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ChangeListingCollate extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{

    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange('listing', 'MODIFY description MEDIUMTEXT COLLATE utf8mb4_0900_ai_ci');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange('listing', 'MODIFY description MEDIUMTEXT COLLATE utf8_general_ci');
    }

    protected function getAdditionalArguments(): array
    {
        return [
            '--chunk-time=1'
        ];
    }
}

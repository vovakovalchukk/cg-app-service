<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class UnimportedListingVariationsIdColumnBigint extends AbstractOnlineSchemaChange implements  EnvironmentAwareInterface
{
    protected const TABLE = 'unimportedListingVariation';

    public function up()
    {
        $this->onlineSchemaChange(
            static::TABLE,
            'MODIFY COLUMN `id` bigint NOT NULL AUTO_INCREMENT'
        );
    }

    public function down()
    {
        // we probably don't want to reverse this
    }

    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }
}

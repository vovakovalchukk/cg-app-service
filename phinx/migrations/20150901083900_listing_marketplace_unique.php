<?php
use Phinx\Migration\AbstractMigration;

class ListingMarketplaceUnique extends AbstractMigration
{
    const TABLE = 'listing';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this
            ->table(static::TABLE)
            ->removeIndex(['accountId', 'externalId'])
            ->changeColumn('marketplace', 'string', ['null' => false])
            ->addIndex(['accountId', 'externalId', 'marketplace'], ['unique' => true])
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this
            ->table(static::TABLE)
            ->removeIndex(['accountId', 'externalId', 'marketplace'])
            ->changeColumn('marketplace', 'string', ['null' => true])
            ->addIndex(['accountId', 'externalId'], ['unique' => true])
            ->update();
    }
}

<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ListingUniqueKey extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function up()
    {
        $this->execute("START TRANSACTION;
            DELETE FROM productToListingMap WHERE listingId NOT IN (SELECT id FROM listing);
            SET foreign_key_checks = 0;
            ALTER IGNORE TABLE listing ADD UNIQUE INDEX accountIdExternalId (accountId, externalId);
            SET foreign_key_checks = 1;
            COMMIT;"
        );
    }

    public function down()
    {
        $orderTable = $this->table('listing');
        $orderTable
            ->removeIndex(['accountId', 'externalId'])
            ->save();
    }
}
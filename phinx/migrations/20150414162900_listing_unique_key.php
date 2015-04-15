<?php
use Phinx\Migration\AbstractMigration;

class ListingUniqueKey extends AbstractMigration
{
    public function up()
    {
        $this->execute("DELETE FROM productToListingMap WHERE listingId NOT IN (SELECT id FROM listing);");
        $this->execute("SET foreign_key_checks = 0;");
        $this->execute("ALTER IGNORE TABLE listing ADD UNIQUE INDEX accountIdExternalId (accountId, externalId);");
        $this->execute("SET foreign_key_checks = 1;");
        $this->execute("DELETE FROM productToListingMap WHERE listingId NOT IN (SELECT id FROM listing);");
    }

    public function down()
    {
        $orderTable = $this->table('listing');
        $orderTable
            ->removeIndex(['accountId', 'externalId'])
            ->save();
    }
}
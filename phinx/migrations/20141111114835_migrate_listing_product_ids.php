<?php

use Phinx\Migration\AbstractMigration;

class MigrateListingProductIds extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            'INSERT IGNORE INTO productToListingMap ( listingId, productId ) ' .
            'SELECT id, productId FROM listing'
        );
    }

    public function down()
    {
        /**
         * Noop
         * Impossible to migrate backwords without truncating mapping table
         */
    }
}

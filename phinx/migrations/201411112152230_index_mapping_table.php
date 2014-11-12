<?php

use Phinx\Migration\AbstractMigration;

class IndexMappingTable extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            'ALTER TABLE productToListingMap ' .
            'ADD INDEX `listingId` (`listingId`), ' .
            'ADD INDEX `productId` (`productId`)'
        );
    }

    public function down()
    {
        $this->execute(
            'ALTER TABLE productToListingMap ' .
            'DROP INDEX `listingId`, ' .
            'DROP INDEX `productId`'
        );
    }
}

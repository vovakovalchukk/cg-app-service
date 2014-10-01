<?php
use Phinx\Migration\AbstractMigration;

class UnimportedListingImageAllowNullMigration extends AbstractMigration
{
    public function change()
    {
        $sql = "ALTER TABLE unimportedListing MODIFY imageId int(11);";

        $this->execute($sql);
    }
}
<?php
use Phinx\Migration\AbstractMigration;

class AccountidListingMigration extends AbstractMigration
{
    public function change()
    {
        $listing = $this->table('listing');
        $listing->addColumn('accountId', 'integer')
            ->update();
    }
}
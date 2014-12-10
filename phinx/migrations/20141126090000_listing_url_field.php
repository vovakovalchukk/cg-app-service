<?php
use Phinx\Migration\AbstractMigration;

class ListingUrlField extends AbstractMigration
{
    const TABLE_NAME = 'listing';

    public function change()
    {
        $this->table(static::TABLE_NAME)
            ->addColumn('url', 'string', ['length' => '2000','null' => true])
            ->update();
    }
}
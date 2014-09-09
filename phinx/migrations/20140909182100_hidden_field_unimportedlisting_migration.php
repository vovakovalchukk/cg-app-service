<?php

use Phinx\Migration\AbstractMigration;

class HiddenFieldUnimportedlistingMigration extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('unimportedListing');
        $table
            ->addColumn('hidden', 'boolean', ['default' => false])
            ->update();
    }
}
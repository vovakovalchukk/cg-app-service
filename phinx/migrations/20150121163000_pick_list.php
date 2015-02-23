<?php

use Phinx\Migration\AbstractMigration;

class PickList extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('pickList');
        $table->addColumn('sortField', 'string')
            ->addColumn('sortDirection', 'string')
            ->addColumn('showPictures', 'boolean')
            ->addColumn('showSkuless', 'boolean')
            ->create();
    }
}
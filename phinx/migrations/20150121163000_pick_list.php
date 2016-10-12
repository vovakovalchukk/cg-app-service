<?php

use Phinx\Migration\AbstractMigration;

class PickList extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('pickList', ['collation' => 'utf8_general_ci']);
        $table->addColumn('sortField', 'string')
            ->addColumn('sortDirection', 'string')
            ->addColumn('showPictures', 'boolean')
            ->addColumn('showSkuless', 'boolean')
            ->create();
    }
}

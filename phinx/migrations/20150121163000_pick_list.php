<?php

use Phinx\Migration\AbstractMigration;

class PickList extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('pickList');
        $table->addColumn('sortBy', 'string')
            ->addColumn('sortDirection', 'string')
            ->addColumn('showPictures', 'boolean')
            ->addColumn('showSkuless', 'boolean')
            ->addColumn('organisationUnitId', 'integer')
            ->addIndex(['organisationUnitId'], ['unique' => true])
            ->create();
    }
}

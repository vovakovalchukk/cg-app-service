<?php

use Phinx\Migration\AbstractMigration;

class TemplateFavourite extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('template')
            ->addColumn('favourite', 'boolean', ['null' => true, 'default' => false])
            ->addIndex(['organisationUnitId', 'favourite'])
            ->update();
    }
}
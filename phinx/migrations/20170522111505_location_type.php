<?php

use Phinx\Migration\AbstractMigration;

use CG\Location\Type;

class LocationType extends AbstractMigration
{
    public function change()
    {
        $this->table('location')
            ->addColumn('type', 'string', ['null' => false, 'default' => Type::MERCHANT])
            ->update();
    }
}
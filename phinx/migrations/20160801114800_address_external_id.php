<?php

use Phinx\Migration\AbstractMigration;

class AddressExternalId extends AbstractMigration
{
    public function change()
    {
        $this->table('address')
            ->addColumn('externalId', 'string', ['null' => true])
            ->update();
    }
}
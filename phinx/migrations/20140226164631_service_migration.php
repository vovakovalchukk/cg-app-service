<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Table\ForeignKey;

class ServiceMigration extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('service');
        $table->addColumn('type', 'string', array('limit' => 25))
            ->addColumn('endpoint', 'string', array('limit' => 255))
            ->create();

        $otherTable = $this->table('serviceEvent');
        $otherTable->addColumn('serviceId', 'integer', array('limit' => 10, 'signed' => false, 'null' => true))
            ->addColumn('type', 'string', array('limit' => 255, 'null' => true))
            ->addColumn('instances', 'integer', array('limit' => 11, 'signed' => false))
            ->addColumn('endpoint', 'string', array('limit' => 255, 'null' => false))
            ->create();
        $otherTable->addForeignKey('serviceId', 'service', 'id', array('delete' => ForeignKey::CASCADE));
    }
}
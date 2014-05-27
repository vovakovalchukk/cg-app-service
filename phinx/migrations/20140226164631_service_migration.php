<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Table\ForeignKey;

class ServiceMigration extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('service', array('id' => false, 'primary_key' => 'id'));
        $table->addColumn('id', 'integer', array('signed' => false, 'autoIncrement' => true))
            ->addColumn('type', 'string', array('limit' => 25))
            ->addColumn('endpoint', 'string', array('limit' => 255))
            ->create();

        $otherTable = $this->table('serviceEvent',  array('id' => false, 'primary_key' => 'id'));
        $otherTable->addColumn('id', 'integer', array('signed' => false, 'autoIncrement' => true))
            ->addColumn('serviceId', 'integer', array('signed' => false, 'null' => true))
            ->addColumn('type', 'string', array('limit' => 255, 'null' => true))
            ->addColumn('instances', 'integer', array('signed' => false))
            ->addColumn('endpoint', 'string', array('limit' => 255, 'null' => false))
            ->addForeignKey('serviceId', 'service', 'id', array('delete' => ForeignKey::CASCADE))
            ->create();
    }
}
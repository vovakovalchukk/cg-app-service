<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;

class PickListLocationNames extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('pickList')
            ->addColumn('showPickingLocations', 'boolean')
            ->update();

        $this
            ->table('pickListLocationNames', ['id' => false, 'primary_key' => ['pickListId', 'level']])
            ->addColumn('pickListId', 'integer', ['null' => false])
            ->addColumn('level', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['null' => false, 'length' => MysqlAdapter::TEXT_LONG])
            ->addForeignKey('pickListId', 'pickList', 'id', ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE])
            ->create();
    }
}
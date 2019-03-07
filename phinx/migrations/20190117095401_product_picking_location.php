<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;

class ProductPickingLocation extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('productPickingLocation', ['id' => false, 'primary_key' => ['productId', 'level']])
            ->addColumn('productId', 'integer', ['null' => false])
            ->addColumn('level', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['null' => false, 'length' => MysqlAdapter::TEXT_LONG])
            ->addIndex('level')
            ->addForeignKey('productId', 'product', 'id', ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE])
            ->create();
    }
}
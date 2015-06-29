<?php
use Phinx\Migration\AbstractMigration;

class AddOriginalOrderId extends AbstractMigration
{
    public function up()
    {
        $this->getAdapter()->beginTransaction();

        foreach ($this->getTables() as $table) {
            $this->table($table)->addColumn('originalId', 'string', ['limit' => 120])->update();
            $this->execute(sprintf('UPDATE `%s` SET `originalId` = `id`', $table));
            $this->table($table)->addIndex(['originalId'], ['unique' => true])->update();
        }

        $this->getAdapter()->commitTransaction();
    }

    public function down()
    {
        $this->getAdapter()->beginTransaction();

        foreach ($this->getTables() as $table) {
            $this->table($table)->removeIndex(['originalId'], ['unique' => true])->update();
            $this->execute(sprintf('UPDATE `%s` SET `id` = `originalId`', $table));
            $this->table($table)->removeColumn('originalId')->update();
        }

        $this->getAdapter()->commitTransaction();
    }

    protected function getTables()
    {
        return ['order', 'orderLive'];
    }
}

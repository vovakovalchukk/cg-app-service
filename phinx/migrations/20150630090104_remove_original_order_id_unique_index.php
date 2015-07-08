<?php
use Phinx\Migration\AbstractMigration;

class RemoveOriginalOrderIdUniqueIndex extends AbstractMigration
{
    public function up()
    {
        foreach ($this->getTables() as $table) {
            $this->table($table)->removeIndex(['originalId'], ['unique' => true])->update();
            $this->table($table)->addIndex(['originalId'])->update();
        }
    }

    public function down()
    {
        foreach ($this->getTables() as $table) {
            $this->table($table)->removeIndex(['originalId'])->update();
            $this->table($table)->addIndex(['originalId'], ['unique' => true])->update();
        }
    }

    protected function getTables()
    {
        return ['order', 'orderLive'];
    }
}

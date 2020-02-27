<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AddOriginalOrderId extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        foreach ($this->getTables() as $table) {
            $this->table($table)->addColumn('originalId', 'string', ['limit' => 120])->update();
            $this->execute(sprintf('UPDATE `%s` SET `originalId` = `id`', $table));
            $this->table($table)->addIndex(['originalId'], ['unique' => true])->update();
        }
    }

    public function down()
    {
        foreach ($this->getTables() as $table) {
            $this->table($table)->removeIndex(['originalId'], ['unique' => true])->update();
            $this->execute(sprintf('UPDATE `%s` SET `id` = `originalId`', $table));
            $this->table($table)->removeColumn('originalId')->update();
        }
    }

    protected function getTables()
    {
        return ['order', 'orderLive'];
    }
}

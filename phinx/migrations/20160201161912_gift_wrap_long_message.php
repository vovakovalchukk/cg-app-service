<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class GiftWrapLongMessage extends AbstractMigration
{
    const TABLE = 'giftWrap';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this
            ->table(static::TABLE)
            ->changeColumn('giftWrapMessage', 'text', ['length' => MysqlAdapter::TEXT_LONG])
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this
            ->table(static::TABLE)
            ->changeColumn('giftWrapMessage', 'string', ['length' => 120])
            ->update();
    }
}

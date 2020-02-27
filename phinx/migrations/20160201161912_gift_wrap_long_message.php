<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class GiftWrapLongMessage extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE = 'giftWrap';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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

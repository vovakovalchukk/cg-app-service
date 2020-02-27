<?php
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class EbayOrderItemId extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('itemVariationAttribute')->dropForeignKey('itemId')->update();
        $this->table('itemVariationAttribute')
            ->addForeignKey('itemId', 'item', 'id', ['update' => ForeignKey::CASCADE])
            ->update();
        if (!$this->hasTable('account.account')) {
            return;
        }
        $sql = 'UPDATE `item` i'
            . ' JOIN `account`.`account` a ON i.`accountId` = a.`id`'
            . ' SET i.`id` = CONCAT_WS("-", i.`accountId`, i.`externalId`)'
            . ' WHERE a.`channel` = "ebay"';

        $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $sql = 'UPDATE `item` i'
            . ' JOIN `account`.`account` a ON i.`accountId` = a.`id`'
            . ' SET i.`id` = CONCAT_WS("-", i.`accountId`, RIGHT(i.`id`, LOCATE("-", REVERSE(i.`id`)) - 1))'
            . ' WHERE a.`channel` = "ebay"';

        $this->execute($sql);

        $this->table('itemVariationAttribute')->dropForeignKey('itemId')->update();
        $this->table('itemVariationAttribute')
            ->addForeignKey('itemId', 'item', 'id')
            ->update();
    }
}

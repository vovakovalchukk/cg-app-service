<?php
use Phinx\Migration\AbstractMigration;

class EbayOrderItemId extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
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
    }
}

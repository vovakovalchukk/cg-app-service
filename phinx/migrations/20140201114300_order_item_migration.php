<?php

use Phinx\Migration\AbstractMigration;

class OrderItemMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `item` (
                    `id` varchar(120) NOT NULL,
                    `orderId` varchar(120) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $sql = 'DROP TABLE IF EXISTS `item`';
        $this->execute($sql);
    }
}

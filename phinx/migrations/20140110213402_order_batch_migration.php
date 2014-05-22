<?php

use Phinx\Migration\AbstractMigration;

class OrderBatchMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `batch` (
                      `id` varchar(30) NOT NULL,
                      `name` int(10) unsigned NOT NULL,
                      `organisationUnitId` int(10) unsigned NOT NULL,
                      `active` tinyint(1),
                      PRIMARY KEY (`id`),
                      KEY `organisationUnitId-name` (`organisationUnitId`, `name`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $sql = 'DROP TABLE `batch`';
        $this->execute($sql);
    }
}

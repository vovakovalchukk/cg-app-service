<?php

use Phpmig\Migration\Migration;

class CreateServiceEventTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS serviceEvent ('
                . ' `id` INT AUTO_INCREMENT,'
                . ' `serviceId` INT UNSIGNED,'
                . ' `type` VARCHAR(255),'
                . ' `instances` INT UNSIGNED NOT NULL,'
                . ' `endpoint` VARCHAR(255) NOT NULL,'
                . ' PRIMARY KEY (`id`),'
                . ' UNIQUE KEY `serviceEventType` (`serviceId`, `type`),'
                . ' CONSTRAINT `serviceEvent` FOREIGN KEY `serviceEvent` (`serviceId`)'
                    . ' REFERENCES service (`id`) ON UPDATE RESTRICT ON DELETE CASCADE'
            . ' ) ENGINE=INNODB';

        $this->getContainer()['db']->query($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $sql = 'DROP TABLE IF EXISTS serviceEvent';

        $this->getContainer()['db']->query($sql);
    }
}

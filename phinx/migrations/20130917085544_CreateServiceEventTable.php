<?php

use Phpmig\Migration\Migration;

class CreateServiceEventTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS service_event ('
                . ' `id` INT AUTO_INCREMENT,'
                . ' `service_id` INT UNSIGNED,'
                . ' `type` VARCHAR(255),'
                . ' `instances` INT UNSIGNED NOT NULL,'
                . ' `endpoint` VARCHAR(255) NOT NULL,'
                . ' PRIMARY KEY (`id`),'
                . ' UNIQUE KEY `service_event_type` (`service_id`, `type`),'
                . ' CONSTRAINT `service_event` FOREIGN KEY `service_event` (`service_id`)'
                    . ' REFERENCES service (`id`) ON UPDATE RESTRICT ON DELETE CASCADE'
            . ' ) ENGINE=INNODB';

        $this->getContainer()['db']->query($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $sql = 'DROP TABLE IF EXISTS service_event';

        $this->getContainer()['db']->query($sql);
    }
}

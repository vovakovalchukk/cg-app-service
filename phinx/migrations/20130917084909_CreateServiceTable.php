<?php
use Phpmig\Migration\Migration;

class CreateServiceTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS service  ('
                . ' `id` INT UNSIGNED AUTO_INCREMENT,'
                . ' `type` VARCHAR(25) NOT NULL,'
                . ' `endpoint` VARCHAR(255) NOT NULL,'
                . ' PRIMARY KEY (`id`)'
            . ' ) ENGINE=INNODB';

        $this->getContainer()['db']->query($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $sql = 'DROP TABLE IF EXISTS service';

        $this->getContainer()['db']->query($sql);
    }
}

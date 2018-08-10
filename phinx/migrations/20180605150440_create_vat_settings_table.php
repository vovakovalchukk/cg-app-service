<?php

use Phinx\Migration\AbstractMigration;

class CreateVatSettingsTable extends AbstractMigration
{
    public function up()
    {
        $this
            ->table('vatSettings', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer')
            ->addColumn('chargeVat', 'boolean', ['null' => true])
            ->create();
        $this->execute('INSERT INTO `vatSettings` (`id`) SELECT DISTINCT `root` FROM `directory`.`organisationUnit`');
    }

    public function down()
    {
        $this->dropTable('vatSettings');
    }
}
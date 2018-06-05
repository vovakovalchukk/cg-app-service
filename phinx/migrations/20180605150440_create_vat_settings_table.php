<?php

use Phinx\Migration\AbstractMigration;

class CreateVatSettingsTable extends AbstractMigration
{
    public function up()
    {
        $this
            ->table('vatSettings', ['id' => false, 'primary_key' => ['organisationUnitId']])
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('chargeVat', 'boolean', ['null' => true])
            ->create();
        $this->execute('INSERT INTO `vatSettings` (`organisationUnitId`) SELECT DISTINCT `root` FROM `directory`.`organisationUnit`');
    }

    public function down()
    {
        $this->dropTable('vatSettings');
    }
}
<?php

use Phinx\Migration\AbstractMigration;

class UsageIndex extends AbstractMigration
{
    public function up()
    {
        $this->execute('ALTER TABLE `usage` ADD INDEX UsageByOU (organisationUnitId, statistic);');
    }

    public function down()
    {
        $this->execute('ALTER TABLE `usage` DROP INDEX UsageByOU;');
    }
}

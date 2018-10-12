<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class UpdateUsageByOuIndex extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $this->onlineSchemaChange(
            'usage',
            'DROP INDEX UsageByOU, ADD INDEX UsageByOU (organisationUnitId,statistic,timestamp)'
        );
    }

    public function down()
    {
        $this->onlineSchemaChange(
            'usage',
            'DROP INDEX UsageByOU, ADD INDEX UsageByOU (organisationUnitId,statistic)'
        );
    }
}

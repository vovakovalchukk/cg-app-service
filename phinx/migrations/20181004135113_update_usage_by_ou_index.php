<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class UpdateUsageByOuIndex extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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

<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class UsageIndex extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->execute('ALTER TABLE `usage` ADD INDEX UsageByOU (organisationUnitId, statistic);');
    }

    public function down()
    {
        $this->execute('ALTER TABLE `usage` DROP INDEX UsageByOU;');
    }
}

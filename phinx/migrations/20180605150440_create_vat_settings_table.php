<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class CreateVatSettingsTable extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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
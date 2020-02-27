<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class EkmRegistration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $table = $this->table("ekmRegistration", ['id' => true, 'collation' => 'utf8_general_ci']);
        $table->addColumn('organisationUnitId', 'integer', ['null' => true])
            ->addColumn('ekmUsername', 'string')
            ->addColumn('json', 'text')
            ->addColumn('referrer', 'string')
            ->addColumn('application', 'string')
            ->addColumn('token', 'string', ['null' => true])
            ->addColumn('createdDate', 'datetime')
            ->addColumn('completedDate', 'datetime', ['null' => true])
            ->create();
    }
}
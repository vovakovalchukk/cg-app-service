<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class UsageMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $table = $this->table('usage');
        $table->addColumn('statistic', 'string')
            ->addColumn('type', 'string')
            ->addColumn('timestamp', 'datetime')
            ->addColumn('amount', 'decimal', ['precision' => 12, 'scale' => 4])
            ->addColumn('organisationUnitId', 'integer')
            ->save();
    }
}
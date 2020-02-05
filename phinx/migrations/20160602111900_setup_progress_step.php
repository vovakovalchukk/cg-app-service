<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class SetupProgressStep extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $product = $this->table('setupProgressStep', ['id' => false, 'collation' => 'utf8_general_ci']);
        $product->addColumn('organisationUnitId', 'integer')
            ->addColumn('name', 'string')
            ->addColumn('status', 'string')
            ->addColumn('modified', 'datetime')
            ->addIndex(['organisationUnitId', 'name'], ['unique' => true])
            ->create();
    }
}

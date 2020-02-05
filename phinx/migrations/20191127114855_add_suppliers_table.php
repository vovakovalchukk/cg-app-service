<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AddSuppliersTable extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('supplier')
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('name', 'string')
            ->addIndex(['organisationUnitId', 'name'], ['unique' => true])
            ->create();
    }
}

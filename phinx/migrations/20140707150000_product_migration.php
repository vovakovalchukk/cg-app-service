<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $table = $this->table('product', ['collation' => 'utf8_general_ci']);
        $table->addColumn('organisationUnitId', 'integer')
            ->addColumn('sku', 'string', ['null' => true])
            ->addColumn('name', 'string')
            ->create();
    }
}

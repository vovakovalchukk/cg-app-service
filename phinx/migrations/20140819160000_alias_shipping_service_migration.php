<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AliasShippingServiceMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $table = $this->table('alias');
        $table->addColumn('accountId', 'integer', ['after' => 'organisationUnitId'])
             ->addColumn('shippingService', 'string', ['after' => 'accountId'])
             ->save();
    }
}
<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductAccountDetail extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('productAccountDetail', ['id' => false, 'primary_key' => ['productId', 'accountId']])
            ->addColumn('productId', 'integer')
            ->addColumn('accountId', 'integer')
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('price', 'decimal', ['precision' => 12, 'scale' => 4, 'null' => true])
            ->create();
    }
}
<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductChannelEbayEpid extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('productEbayEpid', ['id' => false, 'primary_key' => ['productId', 'marketplace']])
            ->addColumn('productId', 'integer')
            ->addColumn('marketplace', 'integer')
            ->addColumn('epid', 'integer')
            ->create();
    }
}

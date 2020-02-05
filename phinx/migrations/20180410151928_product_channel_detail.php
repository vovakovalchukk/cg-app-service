<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductChannelDetail extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('productChannelDetail', ['id' => false, 'primary_key' => ['productId', 'channel'], 'collation' => 'utf8_unicode_ci'])
            ->addColumn('productId', 'integer')
            ->addColumn('channel', 'string')
            ->addColumn('organisationUnitId', 'integer')
            ->create();
    }
}
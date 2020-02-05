<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ListingSkuToExternalIdMap extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function change()
    {
        $this
            ->table('listingSkuToExternalIdMap', ['id' => false, 'primary_key' => ['listingId', 'listingSku'], 'collation' => 'utf8_general_ci'])
            ->addColumn('listingId', 'integer')
            ->addColumn('listingSku', 'string')
            ->addColumn('externalId', 'string')
            ->create();
    }
}
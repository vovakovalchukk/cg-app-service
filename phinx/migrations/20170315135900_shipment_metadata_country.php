<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ShipmentMetadataCountry extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('shipmentMetadataCountry')
            ->addColumn('organisationUnitId', 'integer', ['signed' => false])
            ->addColumn('countryCode', 'string', ['length' => 2])
            ->addIndex(['organisationUnitId', 'countryCode'], ['unique' => true])
            ->create();
    }
}
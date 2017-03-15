<?php
use Phinx\Migration\AbstractMigration;

class ShipmentMetadataCountry extends AbstractMigration
{
    public function change()
    {
        $this->table('shipmentMetadataCountry')
            ->addColumn('organisationUnitId', 'integer', ['signed' => false])
            ->addColumn('countryCode', 'string', ['length' => 2])
            ->addIndex('organisationUnitId')
            ->create();
    }
}
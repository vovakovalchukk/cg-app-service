<?php
use Phinx\Migration\AbstractMigration;

class UnimportedListingVariationSkusMigration extends AbstractMigration
{
    public function change()
    {
        $unimportedListingVariationSkusTable = $this->table(
            'unimportedListingVariationSkus',
            ['id' => false, 'primary_key' => ['unimportedListingId', 'unimportedListingVariationSku']]
        );
        $unimportedListingVariationSkusTable->addColumn('unimportedListingId', 'integer')
            ->addColumn('unimportedListingVariationSku', 'string')
            ->create();
    }
}
<?php
use Phinx\Migration\AbstractMigration;

class UnimportedListingVariationSkusMigration extends AbstractMigration
{
    public function up()
    {
        $unimportedListingVariationSkusTable = $this->table(
            'unimportedListingVariationSkus',
            ['id' => false, 'primary_key' => ['unimportedListingId', 'variationSku']]
        );
        $unimportedListingVariationSkusTable
            ->addColumn('unimportedListingId', 'integer')
            ->addColumn('variationSku', 'string')
            ->create();
    }

    public function down()
    {
        $this->table('unimportedListingVariationSkus')->drop();
    }
}
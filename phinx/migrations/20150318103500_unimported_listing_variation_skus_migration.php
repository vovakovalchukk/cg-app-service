<?php
use Phinx\Migration\AbstractMigration;

class UnimportedListingVariationSkusMigration extends AbstractMigration
{
    public function up()
    {
        $unimportedListingVariationSkusTable = $this->table(
            'unimportedListingVariationSkus'
        );
        $unimportedListingVariationSkusTable
            ->addColumn('unimportedListingId', 'integer')
            ->addColumn('variationSku', 'string')
            ->addIndex(['unimportedListingId', 'variationSku'], ['unique' => true])
            ->create();
    }

    public function down()
    {
        $this->table('unimportedListingVariationSkus')->drop();
    }
}
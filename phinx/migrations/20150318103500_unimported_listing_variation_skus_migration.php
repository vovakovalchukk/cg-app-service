<?php
use Phinx\Migration\AbstractMigration;

class UnimportedListingVariationSkusMigration extends AbstractMigration
{
    public function up()
    {
        $unimportedListingVariationTable = $this->table(
            'unimportedListingVariation',
            ['collation' => 'utf8_general_ci']
        );
        $unimportedListingVariationTable
            ->addColumn('unimportedListingId', 'integer')
            ->addColumn('sku', 'string')
            ->addIndex(['unimportedListingId', 'sku'], ['unique' => true])
            ->create();
    }

    public function down()
    {
        $this->table('unimportedListingVariation')->drop();
    }
}

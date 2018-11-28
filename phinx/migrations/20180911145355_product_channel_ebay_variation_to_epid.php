<?php
use Phinx\Migration\AbstractMigration;

class ProductChannelEbayVariationToEpid extends AbstractMigration
{
    public function up()
    {
        // The existing data is not compatible with the new data structure so it might result in unwanted conflicts
        $this->execute('TRUNCATE `productEbayEpid`');

        $this->table('productEbayEpid')
            ->addColumn('variationToEpid', 'string')
            ->removeColumn('epid')
            ->update();
    }

    public function down()
    {
        $this->table('productEbayEpid')
            ->removeColumn('variationToEpid')
            ->addColumn('epid', 'integer')
            ->update();
    }
}

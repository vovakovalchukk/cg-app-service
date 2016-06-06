<?php
use Phinx\Migration\AbstractMigration;

class MultipleTaxRatesPerOu extends AbstractMigration
{
    const TABLE_MODIFY = 'product';
    const TABLE_NEW = 'productTaxRate';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this
            ->table(static::TABLE_NEW)
            ->addColumn('taxRateCode', 'string')
            ->addColumn('productId', 'integer')
            ->addColumn('ouVatCode', 'string')
            ->create();

        $sqlQuery = "
            INSERT INTO %s (`taxRateCode`, `productId`, `ouVatCode`)
            SELECT `taxRateId`, `id`, 'GB'
            FROM %s
            ";

        $this
            ->execute(sprintf($sqlQuery, static::TABLE_NEW, static::TABLE_MODIFY));

        $this
            ->table(static::TABLE_MODIFY)
            ->removeColumn('taxRateId')
            ->update();
    }

    /**
     * Migrate Down. vendor/bin/phinx migrate -t 20160606112329
     */
    public function down()
    {
        $this
            ->table(static::TABLE_MODIFY)
            ->addColumn('taxRateId', 'string')
            ->create();

        $this->dropTable(static::TABLE_NEW);
    }
}

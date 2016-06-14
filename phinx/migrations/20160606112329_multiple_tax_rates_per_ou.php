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
            ->table(static::TABLE_NEW, ['id' => false])
            ->addColumn('productId', 'integer')
            ->addColumn('taxRateId', 'string')
            ->addColumn('VATCountryCode', 'string')
            ->addIndex(['productId', 'taxRateId'], ['unique' => true])
            ->create();

        $sqlQuery = "
            INSERT INTO %s (`productId`, `taxRateId`, `VATCountryCode`)
            SELECT `id`, `taxRateId`, 'GB'
            FROM %s
            WHERE `taxRateId` != null OR `taxRateId` != ''
            ";

        $this->execute(sprintf($sqlQuery, static::TABLE_NEW, static::TABLE_MODIFY));

        $this
            ->table(static::TABLE_MODIFY)
            ->removeColumn('taxRateId')
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this
            ->table(static::TABLE_MODIFY)
            ->addColumn('taxRateId', 'string')
            ->update();

        $sqlQuery = "
            UPDATE %s p
            INNER JOIN %s ptr ON p.id = ptr.productId
            SET p.taxRateId = ptr.taxRateId
            ";

        $this->execute(sprintf($sqlQuery, static::TABLE_MODIFY, static::TABLE_NEW));

        $this->dropTable(static::TABLE_NEW);
    }
}

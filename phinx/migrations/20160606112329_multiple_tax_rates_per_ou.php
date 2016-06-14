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
            ->addColumn('VATCountryCode', 'string')
            ->addColumn('taxRateId', 'string')
            ->addIndex(['productId', 'VATCountryCode'], ['unique' => true])
            ->create();

        $sqlQuery = "
            INSERT INTO %s (`productId`, `VATCountryCode`, `taxRateId`)
            SELECT `id`, 'GB', `taxRateId`
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
            WHERE ptr.VATCountryCode = 'GB'
            ";

        $this->execute(sprintf($sqlQuery, static::TABLE_MODIFY, static::TABLE_NEW));

        $this->dropTable(static::TABLE_NEW);
    }
}

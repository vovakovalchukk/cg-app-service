<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class MoveStockModeToStockEntity extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_PRODUCT = 'product';
    const TABLE_STOCK = 'stock';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this
            ->addColumns(static::TABLE_STOCK)
            ->migrateData(static::TABLE_PRODUCT, static::TABLE_STOCK)
            ->removeColumns(static::TABLE_PRODUCT);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this
            ->addColumns(static::TABLE_PRODUCT)
            ->migrateData(static::TABLE_STOCK, static::TABLE_PRODUCT)
            ->updateParentProduct()
            ->removeColumns(static::TABLE_STOCK);
    }

    /**
     * @return self
     */
    protected function addColumns($table)
    {
        $this
            ->table($table)
            ->addColumn('stockMode', 'string', ['length' => 10, 'null' => true])
            ->addColumn('stockLevel', 'integer', ['null' => true])
            ->addIndex(['stockMode'])
            ->update();
        return $this;
    }

    /**
     * @return self
     */
    protected function migrateData($fromTable, $toTable)
    {
        $update = <<<SQL
UPDATE `%s` moveTo
JOIN (
    SELECT `organisationUnitId`, `sku`, `stockMode`, `stockLevel`
    FROM `%s`
    WHERE `stockMode` IS NOT NULL OR `stockLevel` IS NOT NULL
    ORDER BY %s
) moveFrom ON moveTo.`organisationUnitId` = moveFrom.`organisationUnitId` AND moveTo.`sku` = moveFrom.`sku`
SET moveTo.`stockMode` = moveFrom.`stockMode`, moveTo.`stockLevel` = moveFrom.`stockLevel`;
SQL;

        $orderBy = '`organisationUnitId`, `sku`';
        if ($fromTable == static::TABLE_PRODUCT) {
            $orderBy .= ', `parentProductId` DESC';
        }
        $this->execute(sprintf($update, $toTable, $fromTable, $orderBy));

        return $this;
    }

    /**
     * @return self
     */
    protected function updateParentProduct()
    {
        $update = <<<SQL
UPDATE `product` parent
JOIN (
    SELECT `parentProductId`, `stockMode`, `stockLevel`
    FROM `product`
    WHERE `parentProductId` > 0 AND (`stockMode` IS NOT NULL OR `stockLevel` IS NOT NULL)
) variation ON parent.`id` = variation.`parentProductId`
SET parent.`stockMode` = variation.`stockMode`, parent.`stockLevel` = variation.`stockLevel`;
SQL;

        $this->execute($update);
        return $this;
    }

    /**
     * @return self
     */
    protected function removeColumns($table)
    {
        $this
            ->table($table)
            ->removeIndex(['stockMode'])
            ->removeColumn('stockMode')
            ->removeColumn('stockLevel')
            ->update();
        return $this;
    }
}

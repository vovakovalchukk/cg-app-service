<?php

use Phinx\Migration\AbstractMigration;

class OrderItemMongoReplacement extends AbstractMigration
{
    const TABLE_NAME = 'item';
    const CILEX_LOCATION = '/../../console/app.php';
    const CILEX_CMD = 'phinx:migrateMongoOrderItemDataToMysql';

    public function up()
    {
        $this->table(static::TABLE_NAME)
            ->addColumn('externalId', 'string')
            ->addColumn('accountId', 'integer')
            ->addColumn('itemName', 'string')
            ->addColumn('itemSku', 'string')
            ->addColumn('individualItemPrice', 'decimal', ['precision' => 12, 'scale' => 4])
            ->addColumn('itemQuantity', 'integer')
            ->addColumn('itemTaxPercentage', 'decimal', ['precision' => 7, 'scale' => 4])
            ->addColumn('individualItemDiscountPrice', 'decimal', ['precision' => 12, 'scale' => 4])
            ->addColumn('itemVariationAttribute', 'string')
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('purchaseDate', 'datetime')
            ->addColumn('status', 'string')
            ->update();
        
        $this->insertMongoData();
    }

    public function down()
    {
        $this->table(static::TABLE_NAME)
            ->removeColumn('externalId')
            ->removeColumn('accountId')
            ->removeColumn('itemName')
            ->removeColumn('itemSku')
            ->removeColumn('individualItemPrice')
            ->removeColumn('itemQuantity')
            ->removeColumn('itemTaxPercentage')
            ->removeColumn('individualItemDiscountPrice')
            ->removeColumn('itemVariationAttribute')
            ->removeColumn('organisationUnitId')
            ->removeColumn('purchaseDate')
            ->removeColumn('status')
            ->update();
    }

    protected function insertMongoData()
    {
        if (file_exists(__DIR__ . static::CILEX_LOCATION)) {
            echo shell_exec('php ' . __DIR__ . static::CILEX_LOCATION . ' ' . static::CILEX_CMD);
        }
    }
}


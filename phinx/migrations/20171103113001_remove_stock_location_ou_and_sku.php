<?php
use Phinx\Migration\AbstractMigration;

class RemoveStockLocationOuAndSku extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this
            ->table('stockLocation')
            ->removeIndex(['organisationUnitId', 'sku'])
            ->removeColumn('organisationUnitId')
            ->removeColumn('sku')
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this
            ->table('stockLocation')
            ->addColumn('organisationUnitId', 'integer', ['after' => 'locationId', 'null' => true])
            ->addColumn('sku', 'string', ['after' => 'organisationUnitId', 'null' => true])
            ->addIndex(['organisationUnitId', 'sku'])
            ->update();

        $this->execute(
            'UPDATE stock s JOIN stockLocation sl ON s.`id` = sl.`stockId` SET sl.`organisationUnitId` = s.`organisationUnitId`, sl.`sku` = s.`sku`'
        );

        $this
            ->table('stockLocation')
            ->changeColumn('organisationUnitId', 'integer', ['null' => false])
            ->changeColumn('sku', 'string', ['null' => false])
            ->update();
    }
}
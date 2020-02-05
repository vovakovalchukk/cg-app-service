<?php
use CG\Stock\Location\TypedEntity;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockLocationType extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this
            ->table('stockLocation')
            ->addColumn('type', 'string', ['null' => false, 'default' => TypedEntity::TYPE_REAL])
            ->update();

        $update = 'UPDATE stockLocation sl'
            . ' JOIN productLink pl ON sl.`sku` LIKE pl.`productSku` AND sl.`organisationUnitId` = pl.`organisationUnitId`'
            . ' SET sl.`type` = "' . TypedEntity::TYPE_LINKED . '"';

        $this->execute($update);
    }

    public function down()
    {
        $this->table('stockLocation')->removeColumn('type')->update();
    }
}
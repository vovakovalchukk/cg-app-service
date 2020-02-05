<?php
use CG\Location\Entity as LocationEntity;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class LocationIncludeStockOnAllChannels extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_NAME = 'location';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table(static::TABLE_NAME)
            ->addColumn('includeStockOnAllChannels', 'boolean', ['null' => false, 'default' => LocationEntity::DEFAULT_INCLUDE_STOCK_ON_ALL_CHANNELS])
            ->update();
    }
}
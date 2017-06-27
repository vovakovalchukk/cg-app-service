<?php

use Phinx\Migration\AbstractMigration;

use CG\Location\Entity as LocationEntity;

class LocationIncludeStockOnAllChannels extends AbstractMigration
{
    const TABLE_NAME = 'location';

    public function change()
    {
        $this->table(static::TABLE_NAME)
            ->addColumn('includeStockOnAllChannels', 'boolean', ['null' => false, 'default' => LocationEntity::DEFAULT_INCLUDE_STOCK_ON_ALL_CHANNELS])
            ->update();
    }
}
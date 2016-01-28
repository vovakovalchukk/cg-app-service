<?php
use Phinx\Migration\AbstractMigration;

class OrderTrackingNumberIndex extends AbstractMigration
{
    const TABLE = 'tracking';

    public function change()
    {
        $this
            ->table(static::TABLE)
            ->addIndex('userId')
            ->addIndex('number')
            ->addIndex(['carrier', 'number'])
            ->update();
    }
}

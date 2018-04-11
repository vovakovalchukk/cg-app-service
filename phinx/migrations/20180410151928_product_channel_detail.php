<?php
use Phinx\Migration\AbstractMigration;

class ProductChannelDetail extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('productChannelDetail', ['id' => false, 'primary_key' => ['productId', 'channel'], 'collation' => 'utf8_unicode_ci'])
            ->addColumn('productId', 'integer')
            ->addColumn('channel', 'string')
            ->addColumn('organisationUnitId', 'integer')
            ->create();
    }
}
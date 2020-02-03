<?php

use Phinx\Migration\AbstractMigration;

class OrderItemRefund extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('orderItemRefund', ['row_format' => 'COMPRESSED'])
            ->addColumn('organisationUnitId', 'integer', ['null' => false])
            ->addColumn('itemId', 'string', ['null' => false])
            ->addColumn('amount', 'decimal', ['precision' => 12, 'scale' => 4, 'null' => false])
            ->addIndex(['organisationUnitId', 'itemId'])
            ->addIndex('itemId')
            ->create();
    }
}
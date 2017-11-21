<?php

use Phinx\Migration\AbstractMigration;

class ProductAndListingNewFields extends AbstractMigration
{
    public function change()
    {
        $this->table('product')
            ->addColumn('description', 'string', ['length' => 4096, 'null' => true, 'after' => 'name'])
            ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 4, 'null' => true, 'after' => 'description'])
            ->addColumn('cost', 'decimal', ['precision' => 10, 'scale' => 4, 'null' => true, 'after' => 'price'])
            ->addColumn('condition', 'string', ['length' => 40, 'null' => true, 'after' => 'cost'])
            ->update();

        $this->table('listing')
            ->addColumn('description', 'string', ['length' => 4096, 'null' => true, 'after' => 'accountId'])
            ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 4, 'null' => true, 'after' => 'description'])
            ->addColumn('cost', 'decimal', ['precision' => 10, 'scale' => 4, 'null' => true, 'after' => 'price'])
            ->addColumn('condition', 'string', ['length' => 40, 'null' => true, 'after' => 'cost'])
            ->update();
    }
}

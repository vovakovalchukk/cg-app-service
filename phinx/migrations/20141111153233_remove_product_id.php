<?php

use Phinx\Migration\AbstractMigration;

class RemoveProductId extends AbstractMigration
{
    public function up()
    {
        $this->table('listing')
            ->dropForeignKey('productId')
            ->removeColumn('productId')
            ->save();
    }

    public function down()
    {
        $this->table('listing')
            ->addColumn('productId', 'integer')
            ->save();
    }
}

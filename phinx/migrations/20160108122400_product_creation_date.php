<?php
use Phinx\Migration\AbstractMigration;

class ProductCreationDate extends AbstractMigration
{
    public function change()
    {
        $this->table('product')
            ->addColumn('cgCreationDate', 'datetime', ['null' => true])
            ->update();
    }
}
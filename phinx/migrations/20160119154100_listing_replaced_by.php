<?php
use Phinx\Migration\AbstractMigration;

class ListingReplacedBy extends AbstractMigration
{
    public function change()
    {
        $this->table('listing')
            ->addColumn('replacedById', 'integer', ['signed' => false, 'null' => true])
            ->update();
    }
}
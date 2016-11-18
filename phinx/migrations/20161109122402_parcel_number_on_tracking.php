<?php
use Phinx\Migration\AbstractMigration;

class ParcelNumberOnTracking extends AbstractMigration
{
    public function change()
    {
        $this->table('tracking')
            ->addColumn('packageNumber', 'integer', ['null' => true])
            ->update();
    }
}

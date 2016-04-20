<?php

use CG\Order\Shared\Tracking\Status;
use Phinx\Migration\AbstractMigration;

class OrderTrackingStatus extends AbstractMigration
{
    public function up()
    {
        $this->table('tracking')
            ->addColumn('status', 'string')
            ->update();

        $this->execute("UPDATE `tracking` SET `status` = '" . Status::SENT . "'");
    }

    public function down()
    {
        $this->table('tracking')
            ->removeColumn('status')
            ->update();
    }
}

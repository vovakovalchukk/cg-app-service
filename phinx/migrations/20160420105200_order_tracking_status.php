<?php
use CG\Order\Shared\Tracking\Status;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderTrackingStatus extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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

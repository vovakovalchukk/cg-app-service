<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AmazonCancelledToZeroShipping extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->execute("UPDATE `order` SET shippingPrice=0 WHERE status='cancelled' && channel='amazon'");
        $this->execute("UPDATE `orderLive` SET shippingPrice=0 WHERE status='cancelled' && channel='amazon'");
    }
}

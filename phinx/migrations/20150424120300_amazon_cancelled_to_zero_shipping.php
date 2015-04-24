<?php
use Phinx\Migration\AbstractMigration;

use CG\Order\Shared\Item\Entity as Item;

class AmazonCancelledToZeroShipping extends AbstractMigration
{
    public function change()
    {
        $this->execute("UPDATE `order` SET shippingPrice=0 WHERE status='cancelled' && channel='amazon'");
    }
}

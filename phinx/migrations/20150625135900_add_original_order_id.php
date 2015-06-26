<?php
use Phinx\Migration\AbstractMigration;

class AddOriginalOrderId extends AbstractMigration
{
    public function change()
    {
        $this->execute("START TRANSACTION;");
        $this->table('order')
            ->addColumn("originalId", "string", ["limit" => 120])
            ->update();
        $this->table('orderLive')
            ->addColumn("originalId", "string", ["limit" => 120])
            ->update();

        $this->execute("UPDATE `order` SET originalId = id");
        $this->execute("UPDATE `orderLive` SET originalId = id");
        $this->execute("COMMIT;");
    }
}

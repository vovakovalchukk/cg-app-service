<?php
use Phinx\Migration\AbstractMigration;

class OrderSequence extends AbstractMigration
{
    public function up()
    {
        $product = $this->table('orderSequence', ['collation' => 'utf8_general_ci']);
        // Auto-increment id field is automatically added by Phinx
        $product->addColumn('orderId', 'string')
            ->addIndex(['orderId'], ['unique' => true])
            ->create();

        // Backfill
        $sql = 'INSERT INTO `orderSequence` (`orderId`) '
            . 'SELECT `id` FROM `order`';
        $this->execute($sql);
    }

    public function down()
    {
        $this->table('orderSequence')->drop();
    }
}

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

        // Trigger an insert when we insert into the order table
        $this->execute(
            'CREATE TRIGGER orderInsert AFTER INSERT ON `order` FOR EACH ROW '
            . 'INSERT INTO `orderSequence` (`orderId`) VALUES (new.id)'
        );

        // Backfill
        $this->execute('INSERT INTO `orderSequence` (`orderId`) SELECT `id` FROM `order`');
    }

    public function down()
    {
        $this->execute('DROP TRIGGER orderInsert');
        $this->table('orderSequence')->drop();
    }
}

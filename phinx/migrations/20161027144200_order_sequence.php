<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderSequence extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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

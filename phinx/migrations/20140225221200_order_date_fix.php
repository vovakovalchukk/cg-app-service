<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderDateFix extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $orderTable = $this->table('order');
        $orderTable->changeColumn('purchaseDate', 'datetime', array('null' => true));
        $orderTable->changeColumn('paymentDate', 'datetime', array('null' => true));
        $orderTable->changeColumn('printedDate', 'datetime', array('null' => true));
        $orderTable->changeColumn('dispatchDate', 'datetime', array('null' => true));
    }

    public function down()
    {
        $orderTable = $this->table('order');
        $orderTable->changeColumn('purchaseDate', 'datetime', array('null' => false));
        $orderTable->changeColumn('paymentDate', 'datetime', array('null' => false));
        $orderTable->changeColumn('printedDate', 'datetime', array('null' => false));
        $orderTable->changeColumn('dispatchDate', 'datetime', array('null' => false));
    }
}
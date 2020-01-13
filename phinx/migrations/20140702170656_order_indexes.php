<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderIndexes extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $orderTable = $this->table('order');
        $orderTable->addIndex('billingAddressId');
        $orderTable->addIndex('shippingAddressId');
        $orderTable->addIndex('organisationUnitId');
        $orderTable->update();

        $orderTagTable = $this->table('orderTag');
        $orderTagTable->addIndex('orderId');
        $orderTagTable->addIndex('organisationUnitId');
        $orderTagTable->update();

        $itemTable = $this->table('item');
        $itemTable->addIndex('orderId');
        $itemTable->update();
    }
    
    public function down()
    {
        $orderTable = $this->table('order');
        $orderTable->removeIndex('billingAddressId');
        $orderTable->removeIndex('shippingAddressId');
        $orderTable->removeIndex('organisationUnitId');
        $orderTable->update();

        $orderTagTable = $this->table('orderTag');
        $orderTagTable->removeIndex('orderId');
        $orderTagTable->removeIndex('organisationUnitId');
        $orderTagTable->update();

        $itemTable = $this->table('item');
        $itemTable->removeIndex('orderId');
        $itemTable->update();
    }
}

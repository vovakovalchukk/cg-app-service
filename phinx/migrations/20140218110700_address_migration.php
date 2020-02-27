<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AddressMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $orderTable = $this->table('order');
        $orderTable->dropForeignKey('billingAddressId');
        $orderTable->dropForeignKey('shippingAddressId');
        $orderTable->removeIndex('billingAddressId');
        $orderTable->removeIndex('shippingAddressId');

        $addressTable = $this->table('address');
        $addressTable->changeColumn('id', 'integer', array('autoIncrement' => false));
        $addressTable->removeIndex('id');
        $addressTable->changeColumn('id', 'string', array('limit' => 40));
        $this->execute('ALTER TABLE `address` ADD PRIMARY KEY(id(40))');

        $orderTable->changeColumn('billingAddressId', 'string', array('limit' => 40));
        $orderTable->changeColumn('shippingAddressId', 'string', array('limit' => 40));
        $orderTable->addForeignKey('shippingAddressId', 'address', 'id');
        $orderTable->addForeignKey('billingAddressId', 'address', 'id');
    }

    public function down()
    {
        $orderTable = $this->table('order');
        $orderTable->dropForeignKey('shippingAddressId');
        $orderTable->dropForeignKey('billingAddressId');
        $orderTable->changeColumn('billingAddressId', 'integer', array('signed'=>false));
        $orderTable->changeColumn('shippingAddressId', 'integer', array('signed'=>false));

        $addressTable = $this->table('address');
        $addressTable->changeColumn('id', 'integer', array('autoIncrement' => true));
        $addressTable->addIndex('id');

        $orderTable = $this->table('order');
        $orderTable->addForeignKey('billingAddressId', 'address', 'id');
        $orderTable->addForeignKey('shippingAddressId', 'address', 'id');
        $orderTable->addIndex('billingAddressId');
        $orderTable->addIndex('shippingAddressId');
    }
}
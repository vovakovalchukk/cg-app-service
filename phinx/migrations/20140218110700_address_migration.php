<?php
use Phinx\Migration\AbstractMigration;

class AddressMigration extends AbstractMigration
{
    public function change()
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
        $this->execute('ALTER TABLE address ADD PRIMARY KEY(id(40))');

        $orderTable->changeColumn('billingAddressId', 'string', array('limit' => 40));
        $orderTable->changeColumn('shippingAddressId', 'string', array('limit' => 40));
        $this->execute('ALTER TABLE `order` ADD CONSTRAINT `order_shippingAddressId` FOREIGN KEY (shippingAddressId) REFERENCES `address` (`id`)');
        $this->execute('ALTER TABLE `order` ADD CONSTRAINT `order_billingAddressId` FOREIGN KEY (billingAddressId) REFERENCES `address` (`id`)');
    }
}
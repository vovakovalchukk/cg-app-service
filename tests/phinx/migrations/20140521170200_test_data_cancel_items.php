<?php

use Phinx\Migration\AbstractMigration;
require_once __DIR__.'/../InsertTestDataTrait.php';

class TestDataCancelItems extends AbstractMigration
{
    use InsertTestDataTrait;

    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->insertTestData('cancel', $this->getCancelData());
        $this->insertTestData('cancelItem', $this->getCancelItemData());
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute('TRUNCATE table `cancel`');
        $this->execute('TRUNCATE table `cancelItem`');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function getCancelData()
    {
        return array(
            [1, 'No Payment Received', '1411-10', 'cancel', 10.99, '2014-10-10 00:00:00']
        );
    }

    protected function getCancelItemData() {
        return array(
            [1, '1411-11', 'test-sku-1', 10, 0.00, 1.99, '1411-10'],
            [1, '1411-11', 'test-sku-1', 0, 19.91, 0.00, '1411-10']
        );
    }
}
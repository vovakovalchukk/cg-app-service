<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter as Adapter;

class CreateInvoiceSettingTable extends AbstractMigration
{
    public function change()
    {
        $this->table('invoiceSetting', ['collation' => 'utf8_general_ci'])
            ->addColumn('default', 'string')
            ->addColumn('sendToFba', 'datetime', ['null' => true])
            ->addColumn('autoEmail', 'datetime', ['null' => true])
            ->addColumn('autoEmailAllowed', 'boolean')
            ->addColumn('emailSendAs', 'string', ['null' => true])
            ->addColumn('emailVerified', 'boolean')
            ->addColumn('emailVerificationStatus', 'string', ['null' => true])
            ->addColumn('emailBcc', 'string', ['null' => true])
            ->addColumn('emailTemplate', 'string', ['limit' => Adapter::TEXT_LONG])
            ->addColumn('copyRequired', 'integer', ['null' => true])
            ->addColumn('productImages', 'boolean')
            ->addColumn('itemBarcodes', 'boolean')
            ->addColumn('itemSku', 'boolean')
            ->addColumn('useVerifiedEmailAddressForAmazonInvoices', 'boolean')
            ->addColumn('productLinks', 'boolean')
            ->addColumn('mongoId', 'string', ['null' => true])
            ->create();
    }
}
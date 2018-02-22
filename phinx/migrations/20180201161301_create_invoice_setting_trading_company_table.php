<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter as Adapter;

class CreateInvoiceSettingTradingCompanyTable extends AbstractMigration
{
    public function change()
    {
        $this->table('invoiceSettingTradingCompany', ['collation' => 'utf8_general_ci'])
            ->addColumn('assignedInvoice', 'string', ['null' => true])
            ->addColumn('emailSendAs', 'string', ['null' => true])
            ->addColumn('emailVerified', 'boolean')
            ->addColumn('emailVerificationStatus', 'string', ['null' => true])
            ->addColumn('invoiceSettingId', 'integer', ['null' => false])
            ->addColumn('mongoId', 'string', ['null' => true])
            ->create();
    }
}
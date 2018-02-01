<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter as Adapter;

class CreateInvoiceSettingTradingCompanyTable extends AbstractMigration
{
    public function change()
    {
        $this->table('invoiceSettingTraduingCompany', ['collation' => 'utf8_general_ci'])
            ->addColumn('assignedInvoice', 'string', ['null' => true])
            ->addColumn('emailSendAs', 'string', ['null' => true])
            ->addColumn('emailVerified', 'boolean')
            ->addColumn('emailVerificationStatus', 'string', ['null' => true])
            ->update();
    }
}
<?php

use Phinx\Migration\AbstractMigration;

class AddInvoiceMappingsEmailSubjectAndTemplate extends AbstractMigration
{
    public function change()
    {
        $this->table('invoiceMapping')
            ->addColumn('emailSubject', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('emailTemplate', 'string', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG, 'null' => true])
            ->update();
    }
}

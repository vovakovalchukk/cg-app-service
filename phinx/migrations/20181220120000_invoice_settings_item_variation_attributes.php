<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class InvoiceSettingsItemVariationAttributes extends AbstractMigration
{
    public function change()
    {
        $this->table('invoiceSetting')
            ->addColumn('itemVariationAttributes', 'boolean', ['default' => false])
            ->update();
    }
}

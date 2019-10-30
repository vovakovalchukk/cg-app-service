<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter as Adapter;

class TemplatePrintAndMultiPerPage extends AbstractMigration
{
    public function change()
    {
        $this->table('template')
            ->addColumn('printPage', 'string', ['null' => true])
            ->addColumn('multiPerPage', 'string', ['null' => true])
            ->update();
    }
}
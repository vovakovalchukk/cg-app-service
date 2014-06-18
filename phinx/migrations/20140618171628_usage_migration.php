<?php

use Phinx\Migration\AbstractMigration;

class UsageMigration extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('usage');
        $table->addColumn('statistic', 'string')
            ->addColumn('type', 'string')
            ->addColumn('timestamp', 'datetime')
            ->addColumn('amount','float')
            ->addColumn('organisationUnitId', 'integer')
            ->save();
    }
}
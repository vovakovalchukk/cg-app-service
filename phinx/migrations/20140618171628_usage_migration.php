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
            ->addColumn('amount', 'decimal', ['precision' => 12, 'scale' => 4])
            ->addColumn('organisationUnitId', 'integer')
            ->save();
    }
}
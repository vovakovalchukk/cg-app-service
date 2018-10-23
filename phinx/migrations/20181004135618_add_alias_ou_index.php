<?php
use Phinx\Migration\AbstractMigration;

class AddAliasOuIndex extends AbstractMigration
{
    public function change()
    {
        $this->table('alias')
            ->addIndex(['organisationUnitId'])
            ->update();
    }
}

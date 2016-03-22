<?php
use Phinx\Migration\AbstractMigration;

class SettingsShippingAliasOptions extends AbstractMigration
{
    public function change()
    {
        $this->table('alias')
            ->addColumn('options', 'string', ['null' => true])
            ->update();
    }
}

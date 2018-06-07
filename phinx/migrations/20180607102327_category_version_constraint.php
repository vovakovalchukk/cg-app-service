<?php

use Phinx\Migration\AbstractMigration;

class CategoryVersionConstraint extends AbstractMigration
{
    public function up()
    {
        $this
            ->table('category')
            ->removeIndex(['channel', 'marketplace', 'accountId'], ['unique' => false])
            ->addIndex(['channel', 'marketplace', 'accountId', 'version'], ['unique' => false]);
    }

    public function down()
    {
        $this
            ->table('category')
            ->removeIndex(['channel', 'marketplace', 'accountId', 'version'], ['unique' => false])
            ->addIndex(['channel', 'marketplace', 'accountId'], ['unique' => false]);
    }
}
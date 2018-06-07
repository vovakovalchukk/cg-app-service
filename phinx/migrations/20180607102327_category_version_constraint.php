<?php

use Phinx\Migration\AbstractMigration;

class CategoryVersionConstraint extends AbstractMigration
{
    public function up()
    {
        $this
            ->table('category')
            ->removeIndex(['externalId', 'channel', 'marketplace'], ['unique' => true])
            ->addIndex(['externalId', 'channel', 'marketplace', 'version'], ['unique' => true]);
    }

    public function down()
    {
        $this
            ->table('category')
            ->removeIndex(['externalId', 'channel', 'marketplace', 'version'], ['unique' => true])
            ->addIndex(['externalId', 'channel', 'marketplace'], ['unique' => true]);
    }
}
<?php

use Phinx\Migration\AbstractMigration;

class RemoveCategoryTableKeyConstraint extends AbstractMigration
{
    public function up()
    {
        $this->table('category')
            ->removeIndexByName('ExternalIdAccountId')
            ->save();
    }

    public function down()
    {
        $this->table('category')
            ->addIndex(['externalId', 'accountId'], ['unique' => true])
            ->save();
    }
}
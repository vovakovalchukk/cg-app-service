<?php

use Phinx\Migration\AbstractMigration;

class CategoryTemplateAccountCategories extends AbstractMigration
{
    public function up()
    {
        $this->execute('DELETE FROM `categoryTemplate`');

        $this->table('categoryTemplateCategory')
            ->addColumn('accountId', 'integer', ['signed' => false, 'null' => false])
            ->removeIndex(['organisationUnitId', 'categoryId'])
            ->addIndex(['organisationUnitId', 'categoryId', 'accountId'], ['unique' => true])
            ->update();
    }

    public function down()
    {
        $this->table('categoryTemplateCategory')
            ->removeIndex(['organisationUnitId', 'categoryId', 'accountId'], ['unique' => true])
            ->addIndex(['organisationUnitId', 'categoryId'])
            ->removeColumn('accountId')
            ->update();
    }
}

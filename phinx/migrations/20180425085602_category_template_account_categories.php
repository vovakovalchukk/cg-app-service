<?php

use Phinx\Migration\AbstractMigration;

class CategoryTemplateAccountCategories extends AbstractMigration
{
    public function change()
    {
        $this->execute('DELETE FROM `categoryTemplate`');

        $this->table('categoryTemplateCategory')->drop();

        $this->table('categoryTemplateCategory', ['id' => false, 'primary_key' => ['categoryTemplateId', 'categoryId'], 'collation' => 'utf8_unicode_ci'])
            ->addColumn('categoryTemplateId', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('accountId', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('categoryId', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('organisationUnitId', 'integer', ['signed' => false, 'null' => false])
            ->addIndex(['organisationUnitId', 'categoryId', 'accountId'], ['unique' => true])
            ->create();
    }
}

<?php

use Phinx\Migration\AbstractMigration;

class CategoryTemplate extends AbstractMigration
{
    public function change()
    {
        $this->table('categoryTemplate', ['collation' => 'utf8_unicode_ci'])
            ->addColumn('organisationUnitId', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('name', 'string', ['null' => false])
            ->addIndex(['organisationUnitId', 'name'], ['unique' => true])
            ->create();

        $this->table('categoryTemplateCategory', ['id' => false, 'primary_key' => ['categoryTemplateId', 'categoryId'], 'collation' => 'utf8_unicode_ci'])
            ->addColumn('categoryTemplateId', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('categoryId', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('organisationUnitId', 'integer', ['signed' => false, 'null' => false])
            ->addIndex(['organisationUnitId', 'categoryId'], ['unique' => true])
            ->create();
    }
}
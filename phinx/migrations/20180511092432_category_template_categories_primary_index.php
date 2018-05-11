<?php

use Phinx\Migration\AbstractMigration;

class CategoryTemplateCategoriesPrimaryIndex extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('categoryTemplateCategory')
            ->removeIndex(['categoryTemplateId', 'categoryId'])
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('categoryTemplateCategory')
            ->addIndex(['categoryTemplateId', 'categoryId'])
            ->update();
    }
}
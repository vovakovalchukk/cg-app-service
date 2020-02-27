<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class CategoryTemplateCategoriesPrimaryIndex extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        // The existing data is not compatible with the new data structure so it might result in unwanted conflicts
        $this->execute('DELETE FROM `categoryTemplate`');
        $this->execute('DELETE FROM `categoryTemplateCategory`');

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
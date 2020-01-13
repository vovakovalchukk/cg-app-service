<?php
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductDetailExtension extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
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
        $this->onlineSchemaChange('productDetail', 'ADD COLUMN `upc` VARCHAR(12) NULL, ADD COLUMN `isbn` VARCHAR(13) NULL', 200);
        $this
            ->table('productCategoryTemplate', ['id' => false, 'primary_key' => ['productId', 'categoryTemplateId']])
            ->addColumn('productId', 'integer')
            ->addColumn('categoryTemplateId', 'integer')
            ->addForeignKey('productId', 'productDetail', 'id', ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('productCategoryTemplate')->drop();
        $this->onlineSchemaChange('productDetail', 'DROP COLUMN `upc`, DROP COLUMN `isbn`');
    }
}
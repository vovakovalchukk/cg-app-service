<?php
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AmazonProductChannelAndCategoryDetail extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('productAmazonDetail', ['id' => false, 'primary_key' => 'productId'])
            ->addColumn('productId', 'integer')
            ->addColumn('conditionNote', 'string', ['null' => true])
            ->create();

        $this
            ->table('productCategoryAmazonDetail', ['id' => false, 'primary_key' => ['productId', 'categoryId']])
            ->addColumn('productId', 'integer')
            ->addColumn('categoryId', 'integer')
            ->addColumn('subCategoryId', 'string', ['null' => true])
            ->create();

        $this
            ->table('productCategoryAmazonItemSpecifics')
            ->addColumn('productId', 'integer')
            ->addColumn('categoryId', 'integer')
            ->addColumn('parentId', 'integer')
            ->addColumn('name', 'string', ['limit' => 190])
            ->addForeignKey(
                ['productId', 'categoryId'],
                'productCategoryAmazonDetail',
                ['productId', 'categoryId'],
                ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE]
            )
            ->addIndex(['productId', 'categoryId'])
            ->addIndex('parentId')
            ->create();

        $this
            ->table('productCategoryAmazonItemSpecificsValues')
            ->addColumn('itemSpecificId', 'integer')
            ->addColumn('value', 'string')
            ->addForeignKey(
                'itemSpecificId',
                'productCategoryAmazonItemSpecifics',
                'id',
                ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE]
            )
            ->create();
    }
}

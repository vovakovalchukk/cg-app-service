<?php

use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;

class AmazonProductChannelAndCategoryDetail extends AbstractMigration
{
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
            ->addColumn('name', 'string', ['limit' => 190])
            ->addForeignKey(
                ['productId', 'categoryId'],
                'productCategoryAmazonDetail',
                ['productId', 'categoryId'],
                ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE]
            )
            ->addIndex(['productId', 'categoryId', 'name'], ['unique' => true])
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

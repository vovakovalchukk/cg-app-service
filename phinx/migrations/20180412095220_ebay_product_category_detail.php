<?php
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;

class EbayProductCategoryDetail extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('productCategoryEbayDetail', ['id' => false, 'primary_key' => ['productId', 'categoryId']])
            ->addColumn('productId', 'integer')
            ->addColumn('categoryId', 'integer')
            ->addColumn('listingDuration', 'string', ['null' => true])
            ->create();

        $this
            ->table(
                'productCategoryEbayItemSpecifics',
                [
                    'id' => false,
                    'primary_key' => ['productId', 'categoryId', 'name', 'value'],
                    'collation' => 'utf8_unicode_ci',
                ]
            )
            ->addColumn('productId', 'integer')
            ->addColumn('categoryId', 'integer')
            ->addColumn('name', 'string')
            ->addColumn('value', 'string')
            ->addForeignKey(
                ['productId', 'categoryId'],
                'productCategoryEbayDetail',
                ['productId', 'categoryId'],
                ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE]
            )
            ->create();
    }
}
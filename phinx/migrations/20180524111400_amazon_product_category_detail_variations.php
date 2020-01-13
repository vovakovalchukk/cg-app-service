<?php
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AmazonProductCategoryDetailVariations extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('productCategoryAmazonDetail')
            ->addColumn('variationTheme', 'string', ['null' => true])
            ->update();

        $this
            ->table('productCategoryAmazonValidValues', [
                'id' => false,
                'primary_key' => ['productId', 'categoryId', 'sku', 'name'],
                'collation' => 'utf8_unicode_ci',
            ])
            ->addColumn('productId', 'integer')
            ->addColumn('categoryId', 'integer')
            ->addColumn('sku', 'string')
            ->addColumn('name', 'string')
            ->addColumn('option', 'string')
            ->addColumn('displayName', 'string')
            ->addForeignKey(
                ['productId', 'categoryId'],
                'productCategoryAmazonDetail',
                ['productId', 'categoryId'],
                ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE]
            )
            ->create();
    }
}

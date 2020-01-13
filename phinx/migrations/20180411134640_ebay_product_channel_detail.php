<?php
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class EbayProductChannelDetail extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('productEbayDetail', ['id' => false, 'primary_key' => 'productId'])
            ->addColumn('productId', 'integer')
            ->addColumn('imageAttributeName', 'string', ['null' => true])
            ->addColumn('dispatchTimeMax', 'integer', ['null' => true])
            ->addColumn('shippingMethod', 'string', ['null' => true])
            ->addColumn('shippingPrice', 'decimal', ['precision' => 12, 'scale' => 4, 'null' => true])
            ->create();

        $this
            ->table('productEbayAttributeImage', ['id' => false, 'primary_key' => ['productId', 'attributeValue'], 'collation' => 'utf8_unicode_ci'])
            ->addColumn('productId', 'integer')
            ->addColumn('attributeValue', 'string')
            ->addColumn('imageId', 'integer', ['null' => true])
            ->addForeignKey('productId', 'productEbayDetail', 'productId', ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE])
            ->create();
    }
}
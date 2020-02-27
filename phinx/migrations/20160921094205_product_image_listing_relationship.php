<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductImageListingRelationship extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $delete = 'DELETE i FROM productImage i LEFT JOIN product p ON i.productId = p.id WHERE p.id IS NULL';
        $this->execute($delete);

        $this
            ->table('productImage')
            ->removeIndex('productId')
            ->addForeignKey('productId', 'product', 'id', ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->update();

        $primaryKey = 'ALTER TABLE productImage ADD PRIMARY KEY (productId, imageId)';
        $this->execute($primaryKey);

        $this
            ->table('productListingImage', ['id' => false, 'primary_key' => ['productId', 'listingId', 'imageId']])
            ->addColumn('productId', 'integer')
            ->addColumn('listingId', 'integer')
            ->addColumn('imageId', 'integer')
            ->addColumn('order', 'integer')
            ->addForeignKey('productId', 'product', 'id', ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addForeignKey('listingId', 'listing', 'id', ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->create();
    }

    public function down()
    {
        $this->table('productListingImage')->drop();

        $this
            ->table('productImage')
            ->dropForeignKey('productId')
            ->addIndex('productId')
            ->update();

        $primaryKey = 'ALTER TABLE productImage DROP PRIMARY KEY';
        $this->execute($primaryKey);
    }
}

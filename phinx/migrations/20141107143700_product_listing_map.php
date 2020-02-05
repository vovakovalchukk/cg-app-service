<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductListingMap extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function up()
    {
        $table = $this->table(
            'productToListingMap',
            [
                'id' => false,
                'primary_key' => [
                    'productId',
                    'listingId'
                ],
                'collation' => 'utf8_general_ci'
            ]
        );

        $table->addColumn('productId', 'integer')
            ->addColumn('listingId', 'integer')
            ->addIndex(
                [
                    'listingId',
                    'productId'
                ]
            )->create();

        $this->execute(
            'INSERT IGNORE INTO productToListingMap ( listingId, productId ) ' .
            'SELECT id, productId FROM listing'
        );

        $this->table('listing')
            ->dropForeignKey('productId')
            ->removeColumn('productId')
            ->save();
    }

    public function down()
    {
        $this->table('listing')
            ->addColumn('productId', 'integer')
            ->save();

        $this->table('productToListingMap')->drop();
    }
}

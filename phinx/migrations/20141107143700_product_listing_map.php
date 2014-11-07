<?php

use Phinx\Migration\AbstractMigration;

class ProductListingMap extends AbstractMigration
{
    public function up()
    {
        $table = $this->table(
            'productToListingMap',
            [
                'id' => false,
                'primary_key' => [
                    'productId',
                    'listingId'
                ]
            ]
        );

        $table->addColumn('productId', 'integer')
            ->addColumn('listingId', 'integer')
            ->create();
    }

    public function down()
    {
        $this->table('productToListingMap')->drop();
    }
}

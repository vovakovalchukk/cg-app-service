<?php
use Phinx\Migration\AbstractMigration;

class UnimportedListingImageAllowNullMigration extends AbstractMigration
{
    const TABLE_NAME = 'unimportedListing';

    public function up()
    {
        $table = $this->table(static::TABLE_NAME);
        $table
            ->changeColumn('imageId', 'integer', ['null' => true])
            ->update();
    }

    public function down()
    {
        $table = $this->table(static::TABLE_NAME);
        $table
            ->changeColumn('imageId', 'integer')
            ->update();
    }
}
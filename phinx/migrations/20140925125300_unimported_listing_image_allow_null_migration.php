<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class UnimportedListingImageAllowNullMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_NAME = 'unimportedListing';

    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

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
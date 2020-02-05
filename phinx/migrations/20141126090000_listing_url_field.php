<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ListingUrlField extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_NAME = 'listing';

    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function change()
    {
        $this->table(static::TABLE_NAME)
            ->addColumn('url', 'string', ['length' => '2000','null' => true])
            ->update();
    }
}
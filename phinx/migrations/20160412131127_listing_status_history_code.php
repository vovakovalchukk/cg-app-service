<?php
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ListingStatusHistoryCode extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_NAME = 'listingStatusHistoryCode';

    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function change()
    {
        $this
            ->table(static::TABLE_NAME, ['id' => false, 'primary_key' => ['listingStatusHistoryId', 'code'], 'collation' => 'utf8_general_ci'])
            ->addColumn('listingStatusHistoryId', 'integer')
            ->addColumn('code', 'integer', ['null' => true])
            ->addForeignKey('listingStatusHistoryId', 'listingStatusHistory', 'id', ['delete' => ForeignKey::CASCADE, 'update' => ForeignKey::CASCADE])
            ->create();
    }
}

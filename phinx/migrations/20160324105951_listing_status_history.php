<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\EnvironmentAwareInterface;

class ListingStatusHistory extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_NAME = 'listingStatusHistory';

    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function change()
    {
        $this
            ->table(static::TABLE_NAME, ['collation' => 'utf8_general_ci'])
            ->addColumn('listingId', 'integer', ['signed' => false])
            ->addColumn('timestamp', 'datetime')
            ->addColumn('status', 'string')
            ->addColumn('message', 'text', ['length' => MysqlAdapter::TEXT_LONG])
            ->addIndex('listingId')
            ->addIndex('status')
            ->create();
    }
}

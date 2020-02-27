<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ListingTemplates extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function change()
    {
        $this
            ->table('listingTemplate', ['row_format' => 'COMPRESSED'])
            ->addColumn('organisationUnitId', 'integer', ['null' => false])
            ->addColumn('channel', 'string', ['null' => true])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('template', 'string', ['null' => false, 'length' => MysqlAdapter::TEXT_LONG])
            ->create();
    }
}
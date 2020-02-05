<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductCategory extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_NAME = 'category';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table(static::TABLE_NAME)
            ->addColumn('externalId', 'string', ['length' => '155', 'null' => false])
            ->addColumn('parentId', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('title', 'string', ['null' => false])
            ->addColumn('description', 'string', ['null' => true])
            ->addColumn('channel', 'string', ['length' => '80', 'null' => false])
            ->addColumn('marketplace', 'string', ['length' => '20', 'null' => true])
            ->addColumn('listable', 'boolean', ['null' => true])
            ->addColumn('enabled', 'boolean', ['null' => true])
            ->addColumn('version', 'integer', ['null' => true])
            ->addIndex('externalId')
            ->addIndex('parentId')
            ->addIndex('marketplace')
            ->addIndex('listable')
            ->addIndex('enabled')
            ->addIndex('version')
            ->addIndex(['externalId', 'channel', 'marketplace'], ['unique' => true])
            ->create();
    }
}

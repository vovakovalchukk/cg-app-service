<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class CategoryVersionMap extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('categoryVersionMap')
            ->create();

        $this
            ->table('categoryVersionMapChannel')
            ->addColumn('categoryVersionMapId', 'integer')
            ->addColumn('channel', 'string', ['length' => '80'])
            ->addColumn('marketplace', 'string', ['length' => '20', 'null' => true])
            ->addColumn('accountId', 'integer', ['null' => true])
            ->addColumn('version', 'integer')
            ->addForeignKey('categoryVersionMapId', 'categoryVersionMap', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['channel', 'marketplace', 'accountId'], ['unique' => false])
            ->create();
    }
}
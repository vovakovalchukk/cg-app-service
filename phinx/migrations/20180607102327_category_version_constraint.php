<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class CategoryVersionConstraint extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this
            ->table('category')
            ->removeIndex(['externalId', 'channel', 'marketplace'], ['unique' => true])
            ->addIndex(['externalId', 'channel', 'marketplace', 'version'], ['unique' => true])
            ->save();
    }

    public function down()
    {
        $this
            ->table('category')
            ->removeIndex(['externalId', 'channel', 'marketplace', 'version'], ['unique' => true])
            ->addIndex(['externalId', 'channel', 'marketplace'], ['unique' => true])
            ->save();
    }
}
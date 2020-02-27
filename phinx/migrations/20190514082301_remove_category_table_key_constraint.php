<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class RemoveCategoryTableKeyConstraint extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->table('category')
            ->removeIndexByName('ExternalIdAccountId')
            ->save();
    }

    public function down()
    {
        $this->table('category')
            ->addIndex(['externalId', 'accountId'], ['unique' => true])
            ->save();
    }
}
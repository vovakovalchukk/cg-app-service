<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class CreateAliasRuleTable extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $aliasRuleTable = $this->table('aliasRule', ['id' => true, 'primary_key' => 'id', 'collation' => 'utf8_general_ci']);
        $aliasRuleTable->addColumn('shippingAliasId', 'integer')
            ->addColumn('type', 'string')
            ->addColumn('operator', 'string')
            ->addColumn('value', 'string')
            ->addColumn('priority', 'integer')
            ->addForeignKey('shippingAliasId', 'alias', 'id',
                ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}

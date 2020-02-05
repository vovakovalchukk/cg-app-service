<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AliasNullMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $table = $this->table('alias');
        $table->changeColumn('accountId', 'integer', ['null' => true])
             ->changeColumn('shippingService', 'string', ['null' => true])
             ->save();
    }

    public function down()
    {

    }
}
<?php
use Phinx\Migration\AbstractMigration;

class AliasNullMigration extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('alias');
        $table->changeColumn('accountId', 'integer', ['null' => true])
             ->changeColumn('shippingService', 'string', ['null' => true])
             ->save();
    }

    public function down()
    {
        $table = $this->table('alias');
        $table->changeColumn('accountId', 'integer', ['null' => false])
             ->changeColumn('shippingService', 'string', ['null' => false])
             ->save();
    }
}
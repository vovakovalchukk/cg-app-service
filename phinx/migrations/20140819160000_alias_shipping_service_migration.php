<?php
use Phinx\Migration\AbstractMigration;

class AliasShippingServiceMigration extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('alias');
        $table->addColumn('accountId', 'integer', ['after' => 'organisationUnitId'])
             ->addColumn('shippingService', 'string', ['after' => 'accountId'])
             ->save();
    }
}
<?php
use Phinx\Migration\AbstractMigration;

class AliasMigration extends AbstractMigration
{
    public function change()
    {
        $aliasTable = $this->table('alias', ['collation' => 'utf8_general_ci']);
        $aliasTable->addColumn('name', 'string')
                  ->addColumn('organisationUnitId', 'integer')
                  ->create();

        $aliasMethodTable = $this->table('aliasMethod', ['id' => false, 'collation' => 'utf8_general_ci']);
        $aliasMethodTable->addColumn('aliasId', 'integer')
                        ->addColumn('methodId', 'integer')
                        ->addForeignKey('aliasId', 'alias', 'id',
                            ['delete' => 'CASCADE', 'update' => 'NOACTION'])
                        ->addForeignKey('methodId', 'shippingMethod', 'id',
                            ['delete' => 'CASCADE', 'update' => 'NOACTION'])
                        ->create();
    }
}

<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ListingMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function change()
    {
        $table = $this->table('listing', ['collation' => 'utf8_general_ci']);
        $table
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('productId', 'integer')
            ->addColumn('externalId', 'string')
            ->addColumn('channel', 'string')
            ->addColumn('status', 'string')
            ->addForeignKey('productId', 'product', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex('organisationUnitId')
            ->addIndex('productId')
            ->create();
    }
}

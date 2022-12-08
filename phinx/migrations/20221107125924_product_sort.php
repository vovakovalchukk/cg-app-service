<?php
use Phinx\Migration\AbstractMigration;

class ProductSort extends AbstractMigration
{
    protected const TABLE = 'productSort';

    public function supportsEnvironment($environment): bool
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table(static::TABLE, ['row_format' => 'COMPRESSED'])
            ->addColumn('organisationUnitId', 'integer', ['null' => false])
            ->addColumn('userId', 'integer', ['null' => true])
            ->addColumn('data', 'json')
            ->addIndex(['organisationUnitId', 'userId'])
            ->create();
    }
}

<?php
use Phinx\Migration\AbstractMigration;

class ProductFilter extends AbstractMigration
{
    protected const TABLE = 'productFilter';

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
            ->addColumn('filters', 'json')
            ->addColumn('defaultFilter', 'boolean')
            ->addIndex(['organisationUnitId', 'userId'])
            ->create();
    }
}

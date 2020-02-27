<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductDetail extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('productDetail', ['collation' => 'utf8_general_ci'])
            ->addColumn('organisationUnitId', 'integer', ['signed' => false])
            ->addColumn('sku', 'string')
            ->addColumn('weight', 'decimal', ['precision' => 12, 'scale' => 7, 'null' => true])
            ->addColumn('width', 'decimal', ['precision' => 12, 'scale' => 6, 'null' => true])
            ->addColumn('height', 'decimal', ['precision' => 12, 'scale' => 6, 'null' => true])
            ->addColumn('length', 'decimal', ['precision' => 12, 'scale' => 6, 'null' => true])
            ->addIndex(['organisationUnitId', 'sku'], ['unique' => true])
            ->create();
    }
}

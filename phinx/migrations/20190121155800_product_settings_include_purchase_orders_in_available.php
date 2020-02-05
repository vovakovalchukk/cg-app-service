<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductSettingsIncludePurchaseOrdersInAvailable extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('productSettings')
            ->addColumn('includePurchaseOrdersInAvailable', 'boolean', ['default' => 0])
            ->update();
    }
}

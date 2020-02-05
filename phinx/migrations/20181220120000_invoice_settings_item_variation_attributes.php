<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class InvoiceSettingsItemVariationAttributes extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('invoiceSetting')
            ->addColumn('itemVariationAttributes', 'boolean', ['default' => false])
            ->update();
    }
}

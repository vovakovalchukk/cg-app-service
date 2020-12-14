<?php
use Phinx\Migration\EnvironmentAwareInterface;
use Phinx\Migration\AbstractMigration;

class InvoiceSettingsAdditionalShippingLabels extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('invoiceSetting')
            ->addColumn('additionalShippingLabels', 'boolean', ['default' => false])
            ->update();
    }
}

<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderInvoiceEmail extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        foreach (['order', 'orderLive'] as $table) {
            $this
                ->table($table)
                ->addColumn('emailDate', 'datetime', ['null' => true])
                ->update();
        }
    }
}

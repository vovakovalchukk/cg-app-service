<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AddInvoiceMappingsEmailSubjectAndTemplate extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('invoiceMapping')
            ->addColumn('emailSubject', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('emailTemplate', 'string', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true])
            ->update();
    }
}

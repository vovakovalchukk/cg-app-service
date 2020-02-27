<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class TemplatePrintAndMultiPerPage extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('template')
            ->addColumn('printPage', 'string', ['null' => true])
            ->addColumn('multiPerPage', 'string', ['null' => true])
            ->update();
    }
}
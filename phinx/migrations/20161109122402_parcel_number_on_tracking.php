<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ParcelNumberOnTracking extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('tracking')
            ->addColumn('packageNumber', 'integer', ['null' => true])
            ->update();
    }
}

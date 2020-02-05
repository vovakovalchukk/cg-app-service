<?php
use CG\Location\Type;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class LocationType extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('location')
            ->addColumn('type', 'string', ['null' => false, 'default' => Type::MERCHANT])
            ->update();
    }
}
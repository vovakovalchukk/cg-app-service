<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class RemoveUniqueConstraintUserPreferenceTable extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('userPreference', ['collation' => 'utf8_general_ci'])
            ->removeIndex(['mongoId'], ['unique' => true])
            ->changeColumn('mongoId', 'string', ['null' => true])
            ->update();
    }
}
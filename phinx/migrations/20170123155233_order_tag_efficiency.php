<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderTagEfficiency extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('orderTag')->addIndex(['orderTag', 'organisationUnitId'])->update();

        $this
            ->table('orderTags', ['id' => false, 'primary_key' => ['orderTag', 'organisationUnitId'], 'collation' => 'utf8_general_ci'])
            ->addColumn('orderTag', 'string', ['length' => 120])
            ->addColumn('organisationUnitId', 'integer', ['length' => 10, 'signed' => false])
            ->addIndex(['organisationUnitId'])
            ->create();

        $this->getAdapter()->execute('INSERT INTO orderTags SELECT DISTINCT `orderTag`, `organisationUnitId` FROM orderTag');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('orderTags')->drop();
        $this->table('orderTag')->removeIndex(['orderTag', 'organisationUnitId'])->update();
    }
}

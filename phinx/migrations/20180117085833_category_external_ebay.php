<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\EnvironmentAwareInterface;

class CategoryExternalEbay extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_NAME_FEATURE = 'ebayCategoryFeatures';
    const TABLE_NAME_FEATURE_DEFAULTS = 'ebayCategoryFeatureDefaults';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table(static::TABLE_NAME_FEATURE_DEFAULTS, ['collation' => 'utf8_general_ci'])
            ->addColumn('siteId', 'integer')
            ->addColumn('featureDefinitions', 'string', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('siteDefaults', 'string', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addIndex('siteId')
            ->create();

        $this->table(static::TABLE_NAME_FEATURE, ['collation' => 'utf8_general_ci'])
            ->addColumn('categoryId', 'integer', ['signed' => false])
            ->addColumn('features', 'string', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addIndex('categoryId')
            ->create();
    }
}

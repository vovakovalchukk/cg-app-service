<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AddReturnsPolicyToProductCategoryEbayDetail extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('productCategoryEbayDetail')
            ->addColumn('returnPolicy', 'string', ['limit' => MysqlAdapter::TEXT_TINY, 'null' => true])
            ->save();
    }
}
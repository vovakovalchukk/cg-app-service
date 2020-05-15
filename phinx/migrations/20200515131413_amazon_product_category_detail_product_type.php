<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AmazonProductCategoryDetailProductType extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('productCategoryAmazonDetail')
            ->addColumn('productType', 'string', ['null' => true])
            ->update();
    }
}

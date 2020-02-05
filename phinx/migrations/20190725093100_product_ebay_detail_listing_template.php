<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductEbayDetailListingTemplate extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('productEbayDetail')
            ->addColumn('listingTemplateId', 'integer', ['null' => true])
            ->update();
    }
}
<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class UnimportedlistingAccountidExternalidMarketplaceKey extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function change()
    {
        $this->table('unimportedListing')
            ->removeIndex('accountId')
            ->addIndex(['accountId', 'externalId', 'marketplace'])
            ->update();
    }
}

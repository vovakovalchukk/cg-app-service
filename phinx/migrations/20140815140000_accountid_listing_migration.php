<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AccountidListingMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function change()
    {
        $listing = $this->table('listing');
        $listing->addColumn('accountId', 'integer')
            ->update();
    }
}
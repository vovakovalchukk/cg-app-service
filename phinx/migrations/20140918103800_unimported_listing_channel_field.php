<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class UnimportedListingChannelField extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function change()
    {
        $table = $this->table('unimportedListing');
        $table
            ->addColumn('channel', 'string')
            ->update();
    }
}

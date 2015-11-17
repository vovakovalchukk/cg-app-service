<?php
use Phinx\Migration\AbstractMigration;

class UnimportedlistingAccountidExternalidMarketplaceKey extends AbstractMigration
{
    public function change()
    {
        $this->table('accountId')
            ->removeIndex('accountId')
            ->addIndex(['accountId', 'externalId', 'marketplace'])
            ->update();
    }
}

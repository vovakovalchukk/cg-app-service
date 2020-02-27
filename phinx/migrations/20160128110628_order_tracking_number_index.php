<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderTrackingNumberIndex extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE = 'tracking';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table(static::TABLE)
            ->addIndex('userId')
            ->addIndex('number')
            ->addIndex(['carrier', 'number'])
            ->update();
    }
}

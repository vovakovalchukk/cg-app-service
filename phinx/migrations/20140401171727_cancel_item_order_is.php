<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class CancelItemOrderIs extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $cancelItem = $this->table('cancelItem');
        $cancelItem->addColumn('orderId', 'string');
        $cancelItem->update();
    }
}
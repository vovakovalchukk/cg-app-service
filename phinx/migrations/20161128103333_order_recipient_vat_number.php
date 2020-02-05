<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderRecipientVatNumber extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('order')
            ->addColumn('recipientVatNumber', 'string', ['null' => true])
            ->update();

        $this->table('orderLive')
            ->addColumn('recipientVatNumber', 'string', ['null' => true])
            ->update();
    }
}
<?php
use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderItemVariationsDeleteCascade extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this
            ->table('itemVariationAttribute')
            ->dropForeignKey('itemId')
            ->addForeignKey('itemId', 'item', 'id', ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE])
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this
            ->table('itemVariationAttribute')
            ->dropForeignKey('itemId')
            ->addForeignKey('itemId', 'item', 'id', ['update' => ForeignKey::CASCADE])
            ->update();
    }
}

<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Table\ForeignKey;

class OrderItemVariationsDeleteCascade extends AbstractMigration
{
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

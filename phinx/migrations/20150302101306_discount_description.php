<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class DiscountDescription extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->updateTable('order');
        $this->updateTable('orderLive');
    }

    protected function updateTable($tableName)
    {
        $table = $this->table($tableName);
        $table->addColumn(
            'discountDescription',
            'string', [
            'after' => 'totalDiscount',
            'length' => 255,
            'null' => true
        ])->update();
    }
}

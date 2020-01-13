<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ConvertEbayOrderIds extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->execute($this->getOrdersUpdateSql('CONCAT_WS("-", o.accountId, e.ebayOrderId)'));
        foreach ($this->getAffectedTables() as $table => $column) {
            $this->execute($this->getAffectedTableUpdateSql($table, $column, 'originalId', 'id'));
        }
    }

    public function down()
    {
        foreach ($this->getAffectedTables() as $table => $column) {
            $this->execute($this->getAffectedTableUpdateSql($table, $column, 'id', 'originalId'));
        }
        $this->execute($this->getOrdersUpdateSql('o.originalId'));
    }

    protected function getOrdersUpdateSql($id)
    {
        $sql = 'UPDATE cg_app.`order` o JOIN ebay.orderAdditional e ON o.id = e.id SET o.id = %1$s, e.id = %1$s';
        return sprintf($sql, $id);
    }

    protected function getAffectedTables()
    {
        return [
            'cg_app.orderLive' => 'id',
            'cg_app.item' => 'orderId',
            'cg_app.orderTag' => 'orderId',
            'cg_app.note' => 'orderId',
            'cg_app.tracking' => 'orderId',
            'cg_app.alert' => 'orderId',
            'cg_app.cancel' => 'orderId',
            'cg_app.cancelItem' => 'orderId',
        ];
    }

    protected function getAffectedTableUpdateSql($table, $column, $join, $value)
    {
        $sql = 'UPDATE cg_app.`order` o JOIN %1$s t ON o.%3$s = t.%2$s SET t.%2$s = o.%4$s';
        return sprintf($sql, $table, $column, $join, $value);
    }
}

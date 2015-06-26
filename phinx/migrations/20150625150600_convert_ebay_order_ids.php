<?php
use Phinx\Migration\AbstractMigration;

class ConvertEbayOrderIds extends AbstractMigration
{
    public function up()
    {
        $orderIds = $this->fetchAll($this->getEbayOrderIdQuery());

        foreach ($orderIds as $orderId) {
            $newId = $orderId["accountId"] . "-" . $orderId["ebayOrderId"];
            $this->execute($this->getUpdate($newId, $orderId["originalId"]));
        }
    }

    public function down()
    {
        // TODO
        // Use originalId in cg_app.order table to revert the change
    }

    protected function getEbayOrderIdQuery()
    {
        return <<<SQL
SELECT orderAdditional.id AS originalId, orderAdditional.ebayOrderId, `order`.accountId
FROM cg_app.`order`
RIGHT JOIN ebay.orderAdditional
ON `order`.id=orderAdditional.id;
SQL;
    }

    protected function getUpdate($newId, $originalId)
    {
        $sql = "START TRANSACTION;";
        $updateTemplate = $this->getUpdateOrderIdQuery();
        foreach ($this->getAffectedTables() as $table => $column) {
            $sql .= sprintf($updateTemplate, $table, $column, $newId, $column, $originalId);
        }
        return $sql . "COMMIT;";
    }

    protected function getAffectedTables()
    {
        return [
            "ebay.orderAdditional" => "id",
            "cg_app.`order`" => "id",
            "cg_app.orderLive" => "id",
            "cg_app.item" => "orderId",
            "cg_app.orderTag" => "orderId",
            "cg_app.note" => "orderId",
            "cg_app.tracking" => "orderId",
            "cg_app.alert" => "orderId",
            "cg_app.cancel" => "orderId",
            "cg_app.cancelItem" => "orderId",
        ];
    }

    protected function getUpdateOrderIdQuery()
    {
        return <<<SQL
UPDATE %s
SET %s="%s"
WHERE %s="%s";
SQL;
    }
}

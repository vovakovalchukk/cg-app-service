<?php

use Phinx\Migration\AbstractMigration;

class MigrateProductsToRootOus extends AbstractMigration
{
    public function up()
    {
        $productIds = $this->query('SELECT `id`, `organisationUnitId` FROM `product`;');
        foreach ($productIds as $productId) {
            var_dump($productId);
        }
        throw new \Exception('Dont finish migration');
    }

    public function down()
    {
        // Noop
    }
}

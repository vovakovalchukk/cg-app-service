<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class MigrateProductsToRootOus extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $products = $this->query('SELECT `id`, `organisationUnitId` FROM `product`;');
        foreach ($products as $product) {
            $sql = <<<SQL
UPDATE `product` SET `organisationUnitId` =
(
    SELECT IFNULL(
        GROUP_CONCAT(parents.id ORDER BY parents.`left` DESC),
        {$product['organisationUnitId']}
    ) AS `organisationUnitId`
    FROM `directory`.`organisationUnit` AS `ou`
    LEFT JOIN `directory`.`organisationUnit` AS `parents`
    ON `ou`.`left` > `parents`.`left` AND `ou`.`right` < `parents`.`right`
    WHERE `ou`.`id` = {$product['organisationUnitId']}
    GROUP BY `ou`.`id` HAVING ou.id
    IS NOT NULL ORDER BY `parents`.`left` DESC
) WHERE `id` = {$product['id']};
SQL;
            $this->execute($sql);
        }
    }

    public function down()
    {
        // Noop
    }
}

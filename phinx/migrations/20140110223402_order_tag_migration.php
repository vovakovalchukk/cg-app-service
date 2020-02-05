<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderTagMigration extends AbstractMigration implements EnvironmentAwareInterface
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
        $sql = "CREATE TABLE IF NOT EXISTS `orderTag` (
                    `id` varchar(120) NOT NULL,
                    `orderId` varchar(120) NOT NULL,
                    `orderTag` varchar(120) NOT NULL,
                    `organisationUnitId` int(10) unsigned NOT NULL,
                    PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $sql = 'DROP TABLE IF EXISTS `orderTag`';
        $this->execute($sql);
    }
}

<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class NullDateTime extends AbstractMigration implements EnvironmentAwareInterface
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
        $this->query('UPDATE `order` SET `purchaseDate` = null WHERE `purchaseDate` = "0000-00-00 00:00:00"');
        $this->query('UPDATE `order` SET `paymentDate` = null WHERE `paymentDate` = "0000-00-00 00:00:00"');
        $this->query('UPDATE `order` SET `printedDate` = null WHERE `printedDate` = "0000-00-00 00:00:00"');
        $this->query('UPDATE `order` SET `dispatchDate` = null WHERE `dispatchDate` = "0000-00-00 00:00:00"');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Null Op - Was a bug that caused this incorrect data so not something we want to put back :)
    }
}

<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductAmazonDetailBulletPointSearchTerm extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    const TABLE = 'productAmazonDetail';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $migration = <<<SQL
ADD COLUMN `bulletPoint1` VARCHAR(500),
ADD COLUMN `bulletPoint2` VARCHAR(500),
ADD COLUMN `bulletPoint3` VARCHAR(500),
ADD COLUMN `bulletPoint4` VARCHAR(500),
ADD COLUMN `bulletPoint5` VARCHAR(500),
ADD COLUMN `searchTerm1` VARCHAR(250),
ADD COLUMN `searchTerm2` VARCHAR(250),
ADD COLUMN `searchTerm3` VARCHAR(250),
ADD COLUMN `searchTerm4` VARCHAR(250),
ADD COLUMN `searchTerm5` VARCHAR(250)
SQL;
        $this->onlineSchemaChange(static::TABLE, $migration);
    }

    public function down()
    {
        $migration = <<<SQL
DROP COLUMN `bulletPoint1`,
DROP COLUMN `bulletPoint2`,
DROP COLUMN `bulletPoint3`,
DROP COLUMN `bulletPoint4`,
DROP COLUMN `bulletPoint5`,
DROP COLUMN `searchTerm1`,
DROP COLUMN `searchTerm2`,
DROP COLUMN `searchTerm3`,
DROP COLUMN `searchTerm4`,
DROP COLUMN `searchTerm5`
SQL;
        $this->onlineSchemaChange(static::TABLE, $migration);
    }
}
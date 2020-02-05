<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class IncreaseDimensionPrecision extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
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
        $alter = [
            'MODIFY COLUMN `weight` DECIMAL(12,5) NULL',
            'MODIFY COLUMN `width` DECIMAL(12,5) NULL',
            'MODIFY COLUMN `height` DECIMAL(12,5) NULL',
            'MODIFY COLUMN `length` DECIMAL(12,5) NULL',
        ];

        $this->onlineSchemaChange('productDetail', implode(', ', $alter), 200);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $alter = [
            'MODIFY COLUMN `weight` DECIMAL(12,3) NULL',
            'MODIFY COLUMN `width` DECIMAL(12,3) NULL',
            'MODIFY COLUMN `height` DECIMAL(12,3) NULL',
            'MODIFY COLUMN `length` DECIMAL(12,3) NULL',
        ];

        $this->onlineSchemaChange('productDetail', implode(', ', $alter), 200);
    }
}
<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class UpdateProductDetailUpc extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange('productDetail', 'MODIFY `upc` VARCHAR(13)', 200);
    }

    public function down()
    {
        $this->onlineSchemaChange('productDetail', 'MODIFY `upc` VARCHAR(12)', 200);
    }
}
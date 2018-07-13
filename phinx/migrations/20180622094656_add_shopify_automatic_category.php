<?php

use Phinx\Migration\AbstractMigration;

class AddShopifyAutomaticCategory extends AbstractMigration
{
    const AUTOMATIC_CATEGORY_NAME = 'Automatic/Smart';

    public function up()
    {
        $this->deleteAutomaticCategory();
        $categoryName = static::AUTOMATIC_CATEGORY_NAME;
        $this->execute('
            INSERT INTO `category`
              (`externalId`, `title`, `channel`, `listable`, `enabled`, `accountId`)
            VALUES 
              ("automatic", "' . $categoryName . '", "shopify", 1, 1, 0)
        ');
    }

    public function down()
    {
        $this->deleteAutomaticCategory();
    }

    protected function deleteAutomaticCategory(): void
    {
        $categoryName = static::AUTOMATIC_CATEGORY_NAME;
        $this->execute('
            DELETE FROM `category`
            WHERE `category`.`channel` = "shopify"
            and `category`.`title` = "' . $categoryName . '"
            and `category`.`accountId` = 0
        ');
    }
}

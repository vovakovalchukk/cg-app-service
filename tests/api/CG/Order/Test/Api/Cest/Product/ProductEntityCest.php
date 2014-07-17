<?php
namespace CG\Order\Test\Api\Cest\Product;

use CG\Codeception\Cest\Rest\EntityTrait;
use CG\Codeception\Cest\Rest\EntityETagTrait;
use CG\Order\Test\Api\Page\Product\ProductEntityPage;

class ProductEntityCest
{
    use EntityTrait, EntityETagTrait;

    protected function getPageClass()
    {
        return ProductEntityPage::class;
    }
}
 
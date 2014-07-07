<?php
namespace CG\Order\Test\Api\Cest\Product;

use CG\Codeception\Cest\Rest\CollectionTrait;
use CG\Order\Test\Api\Page\Product\ProductPage;

class ProductCest
{
    use CollectionTrait;

    protected function getPageClass()
    {
        return ProductPage::class;
    }
}
 
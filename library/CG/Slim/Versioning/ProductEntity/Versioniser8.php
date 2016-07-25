<?php
namespace CG\Slim\Versioning\ProductEntity;

use CG\Product\Service\Service as ProductService;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Entity as Stock;
use CG\Stock\Service as StockService;
use Nocarrier\Hal;

class Versioniser8 implements VersioniserInterface
{
    /** @var ProductService $productService */
    protected $productService;
    /** @var StockService $stockService */
    protected $stockService;

    public function __construct(ProductService $productService, StockService $stockService)
    {
        $this->setProductService($productService)->setStockService($stockService);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        unset($data['stockMode'], $data['stockLevel']);
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        try {
            $sku = $data['sku'];
            $variation = $response->getFirstResource('variation');
            $variationLink = $response->getFirstLink('variation');

            if ($variation) {
                // Parent Product - Use first variation sku
                $sku = $variation->getData()['sku'];
            } else if ($variationLink) {
                $productId = substr($variationLink, 9);
                $sku = $this->productService->fetch($productId)->getSku();
            }

            /** @var Stock $stock */
            $stock = $this->stockService->fetchBySku($sku, $data['organisationUnitId']);
            $data['stockMode'] = $stock->getStockMode();
            $data['stockLevel'] = ($variation || $variationLink ? null : $stock->getStockLevel());
        } catch (NotFound $exception) {
            // No matching stock means no stock mode
            $data['stockMode'] = $data['stockLevel'] = null;
        }
        $response->setData($data);
    }

    /**
     * @return self
     */
    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setStockService(StockService $stockService)
    {
        $this->stockService = $stockService;
        return $this;
    }
}

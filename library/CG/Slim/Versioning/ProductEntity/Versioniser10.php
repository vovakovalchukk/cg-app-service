<?php
namespace CG\Slim\Versioning\ProductEntity;

use CG\Product\Service\Service as ProductService;
use CG\Product\Entity as Product;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser10 implements VersioniserInterface
{
    /** @var ProductService */
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        if (isset($data['description']) && isset($data['price']) && isset($data['cost']) && isset($data['condition'])) {
            return;
        }

        $data['description'] = $data['price'] = $data['cost'] = $data['condition'] = null;

        if (isset($params['productId'])) {
            try {
                /** @var Product $entity */
                $entity = $this->productService->fetch($params['productId']);
                $data['description'] = $entity->getDescription();
                $data['price'] = $entity->getPrice();
                $data['cost'] = $entity->getCost();
                $data['condition'] = $entity->getCondition();
            } catch (NotFound $exception) {
                // Entity not found so no information to copy
            }
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['description'], $data['price'], $data['cost'], $data['condition']);
        $response->setData($data);
    }
}

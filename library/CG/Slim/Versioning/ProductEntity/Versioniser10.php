<?php
namespace CG\Slim\Versioning\ProductEntity;

use CG\Product\Entity as Product;
use CG\Product\Mapper as ProductMapper;
use CG\Product\Service\Service as ProductService;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser10 implements VersioniserInterface
{
    /** @var ProductService $productService */
    protected $productService;
    /** @var ProductMapper $productMapper */
    protected $productMapper;

    public function __construct(ProductService $productService, ProductMapper $productMapper)
    {
        $this->productService = $productService;
        $this->productMapper = $productMapper;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['id']) && !isset($data['pickingLocations'])) {
            try {
                /** @var Product $product */
                $product = $this->productService->fetch($data['id']);
                $data['pickingLocations'] = $product->getPickingLocations();
            } catch (NotFound $exception) {
                // New entity - nothing to copy
                $data['pickingLocations'] = [];
            }
        }
        $request->setData($data);

        $resources = $request->getResources();
        $embeddedResource = $this->productMapper->getEmbeddedVariationResource();

        if (!isset($resources[$embeddedResource])) {
            return;
        }

        foreach ($resources[$embeddedResource] as $resource) {
            $this->upgradeRequest($params, $resource);
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['pickingLocations']);
        $response->setData($data);

        $resources = $response->getResources();
        $embeddedResource = $this->productMapper->getEmbeddedVariationResource();

        if (!isset($resources[$embeddedResource])) {
            return;
        }

        foreach ($resources[$embeddedResource] as $resource) {
            $this->downgradeResponse($params, $resource, $requestedVersion);
        }
    }
}

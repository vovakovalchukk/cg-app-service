<?php
namespace CG\Slim\Versioning\ProductEntity;

use CG\Product\Entity as Product;
use CG\Product\Mapper as ProductMapper;
use CG\Product\Service\Service as ProductService;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser9 implements VersioniserInterface
{
    /** @var ProductService $productService */
    protected $productService;
    /** @var ProductMapper $productMapper */
    protected $productMapper;

    public function __construct(ProductService $productService, ProductMapper $productMapper)
    {
        $this->setProductService($productService)->setProductMapper($productMapper);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['imageIds'])) {
            $data['images'] = [];
            foreach ($data['imageIds'] as $order => $imageId) {
                $data['images'][] = [
                    'id' => $imageId,
                    'order' => $order,
                ];
            }
            unset($data['imageIds']);
        }
        if (isset($data['id'])) {
            try {
                /** @var Product $product */
                $product = $this->productService->fetch($data['id']);
                $data['listingImages'] = $product->getImageListingIds();
            } catch (NotFound $exception) {
                // New entity - nothing to copy
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
        $data['imageIds'] = [];
        if (isset($data['images'])) {
            $data['imageIds'] = array_column($data['images'], 'id', 'order');
            ksort($data['imageIds'], SORT_NUMERIC);
        }
        unset($data['images'], $data['listingImages']);
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
    protected function setProductMapper(ProductMapper $productMapper)
    {
        $this->productMapper = $productMapper;
        return $this;
    }
}

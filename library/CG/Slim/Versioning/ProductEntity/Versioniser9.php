<?php
namespace CG\Slim\Versioning\ProductEntity;

use CG\Product\Entity as Product;
use CG\Product\Service\Service as ProductService;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser9 implements VersioniserInterface
{
    /** @var ProductService $productService */
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->setProductService($productService);
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
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        $data['imageIds'] = [];
        if (isset($data['images'])) {
            foreach ($data['images'] as $image) {
                $data['imageIds'][$image['order']] = $image['id'];
            }
        }
        unset($data['images'], $data['listingImages']);
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
}

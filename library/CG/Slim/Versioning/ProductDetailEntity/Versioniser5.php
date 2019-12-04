<?php
namespace CG\Slim\Versioning\ProductDetailEntity;

use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser5 implements VersioniserInterface
{
    /** @var Service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['id']) || isset($data['supplierId'])) {
            return;
        }

        try {
            /** @var ProductDetail $productDetail */
            $productDetail = $this->service->fetch($data['id']);
        } catch (NotFound $exception) {
            // Entity not found so no information to copy
            return;
        }

        if (!isset($data['supplierId'])) {
            $data['supplierId'] = $productDetail->getSupplierId();
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['supplierId']);
        $response->setData($data);
    }
}
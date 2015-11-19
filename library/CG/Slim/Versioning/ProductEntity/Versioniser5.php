<?php
namespace CG\Slim\Versioning\ProductEntity;

use CG\Product\Entity as Product;
use CG\Product\Service\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser5 implements VersioniserInterface
{
    /** @var Service $service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($params['productId'])) {
            return;
        }

        try {
            /** @var Product $product */
            $product = $this->service->fetch($params['productId']);
        } catch (NotFound $exception) {
            // Entity not found so no information to copy
            return;
        }

        if (!isset($data['stockMode'])) {
            $data['stockMode'] = $product->getStockMode();
        }

        if (!isset($data['stockLevel'])) {
            $data['stockLevel'] = $product->getStockLevel();
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['stockMode'], $data['stockLevel']);
        $response->setData($data);
    }

    /**
     * @return self
     */
    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
}

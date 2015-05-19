<?php
namespace CG\Slim\Versioning\ProductEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Product\Service\Service;

class Versioniser4 implements VersioniserInterface
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['taxRateId'])) {
            return;
        }

        $data['taxRateId'] = null;
        if (!isset($params['productId'])) {
            $request->setData($data);
            return;
        }
        try {
            $entity = $this->getService()->fetch($params['productId']);
            $data['taxRateId'] = $entity->getTaxRateId();
        } catch (NotFound $exception) {
            // Entity not found so no information to copy
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['taxRateId']);
        $response->setData($data);
    }

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    public function getService()
    {
        return $this->service;
    }
}
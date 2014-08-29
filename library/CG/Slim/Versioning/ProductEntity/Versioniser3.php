<?php
namespace CG\Slim\Versioning\ProductEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Product\Service\Service;

class Versioniser3 implements VersioniserInterface
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['imageIds'])) {
            return;
        }

        $data['imageIds'] = [];
        if (isset($params['productId'])) {
            try {
                $entity = $this->getService()->fetch($params['productId']);
                $data['imageIds'] = $entity->getImageIds();
            } catch (NotFound $exception) {
                // Entity not found so no information to copy
            }
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['imageIds']);
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
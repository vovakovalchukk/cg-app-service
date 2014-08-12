<?php
namespace CG\Slim\Versioning\ProductEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Product\Service;

class Versioniser1 implements VersioniserInterface
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['parentProductId']) && isset($data['attributeNames']) && isset($data['attributeValues'])) {
            return;
        }

        $data['parentProductId'] = 0;
        $data['attributeNames'] = [];
        $data['attributeValues'] = [];

        if (isset($params['id'])) {
            try {
                $entity = $this->getService()->fetch($params['id']);
                $data['parentProductId'] = $entity->getExternalId();
                $data['attributeNames'] = $entity->getAttributeNames();
                $data['attributeValues'] = $entity->getAttributeValues();
            } catch (NotFound $exception) {
                // Entity not found so no information to copy
            }
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['parentProductId'], $data['attributeNames'], $data['attributeValues']);
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
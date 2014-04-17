<?php
namespace CG\Slim\Versioning\OrderItemEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Service\Item\Service;

class Versioniser1 implements VersioniserInterface
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['externalId'])) {
            return;
        }

        $data['externalId'] = null;

        if (isset($params['id'])) {
            try {
                $entity = $this->getService()->fetch($params['id']);
                $data['externalId'] = $entity->getExternalId();
            } catch (NotFound $exception) {
                // Entity not found so no information to copy
            }
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['externalId']);
        $response->setData($data);
    }
}
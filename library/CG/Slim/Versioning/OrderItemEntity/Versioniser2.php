<?php
namespace CG\Slim\Versioning\OrderItemEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Service\Item\Service;

class Versioniser2 implements VersioniserInterface
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
        if (isset($data['purchaseDate']) && isset($data['status'])) {
            return;
        }

        $data['purchaseDate'] = null;
        $data['status'] = null;

        if (isset($params['orderItemId'])) {
            try {
                $entity = $this->getService()->fetch($params['orderItemId']);
                $data['externalId'] = $entity->getExternalId();
                $data['purchaseDate'] = $entity->getPurchaseDate();
                $data['status'] = $entity->getStatus();
            } catch (NotFound $exception) {
                // Entity not found so no information to copy
            }
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['purchaseDate']);
        unset($data['status']);
        $response->setData($data);
    }
}
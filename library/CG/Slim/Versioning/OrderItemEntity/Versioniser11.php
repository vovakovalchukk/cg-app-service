<?php
namespace CG\Slim\Versioning\OrderItemEntity;

use CG\Order\Service\Item\Service as Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser10 implements VersioniserInterface
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
<<<<<<< HEAD
        if (isset($data['dispatchable'])) {
            return;
        }
        $data['dispatchable'] = null;
        if (isset($data['id'])) {
            try {
                $item = $this->service->fetch($data['id']);
                $data['dispatchable'] = $item->getDispatchable();
=======
        if (isset($data['customisation'])) {
            return;
        }
        $data['customisation'] = null;
        if (isset($data['id'])) {
            try {
                $item = $this->service->fetch($data['id']);
                $data['customisation'] = $item->getCustomisation();
>>>>>>> 55707f115aeeaa04b9a9f18164667817c43c9af5
            } catch (NotFound $ex) {
                // No existing item to copy from
            }
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
<<<<<<< HEAD
        unset($data['dispatchable']);
=======
        unset($data['customisation']);
>>>>>>> 55707f115aeeaa04b9a9f18164667817c43c9af5
        $response->setData($data);
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
}
<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Order\Shared\Entity as Order;
use CG\Order\Service\Service;
use CG\Stdlib\Exception\Runtime\NotFound;

class Versioniser2 implements VersioniserInterface
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['fulfilmentChannel'])) {
            $data = $this->setFulfillmentChannelOnData($data);
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['fulfilmentChannel']);
        $response->setData($data);
    }

    protected function setFulfillmentChannelOnData(array $data)
    {
        if (!isset($data["id"])) {
            $data["fulfilmentChannel"] = Order::DEFAULT_FULFILMENT_CHANNEL;
            return $data;
        }

        try {
            $order = $this->getService()->fetch($data["id"]);
            $data["fulfilmentChannel"] = $order->getFulfilmentChannel();
            return $data;
        } catch (NotFound $e) {
            $data["fulfilmentChannel"] = Order::DEFAULT_FULFILMENT_CHANNEL;
            return $data;
        }
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
}

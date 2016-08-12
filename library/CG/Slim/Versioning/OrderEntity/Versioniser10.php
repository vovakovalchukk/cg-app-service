<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser10 implements VersioniserInterface
{
    /**
     * @var Service $service
     */
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['id']) || isset($data['billingExternalId'], $data['shippingExternalId'], $data['fulfilmentExternalId'])) {
            return;
        }
        $data['billingExternalId'] = null;
        $data['shippingExternalId'] = null;
        $data['fulfilmentExternalId'] = null;

        try {
            /** @var \CG\Order\Shared\Entity $order */
            $order = $this->service->fetch($data['id']);
            $data['billingExternalId'] = $order->getBillingAddress()->getExternalId();
            $data['shippingExternalId'] = $order->getShippingAddress()->getExternalId();
            if ($order->getFulfilmentAddress()) {
                $data['fulfilmentExternalId'] = $order->getFulfilmentAddress()->getExternalId();
            }
        } catch (NotFound $e) {
            // New order so there won't be a previously set value
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['billingExternalId'], $data['shippingExternalId'], $data['fulfilmentExternalId']);
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

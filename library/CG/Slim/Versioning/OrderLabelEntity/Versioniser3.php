<?php
namespace CG\Slim\Versioning\OrderLabelEntity;

use CG\Order\Service\Label\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser3 implements VersioniserInterface
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
        if (!isset($data['id']) ||
            isset($data['channelName']) ||
            isset($data['courierName']) ||
            isset($data['courierService']) ||
            isset($data['deliveryInstructions']) ||
            isset($data['signature']) ||
            isset($data['parcels']) ||
            isset($data['insurance']) ||
            isset($data['insuranceMonetary'])
        ) {
            return;
        }

        try {
            $orderLabel = $this->service->fetch($data['id']);
            $data['channelName'] = $orderLabel->getChannelName();
            $data['courierName'] = $orderLabel->getCourierName();
            $data['courierService'] = $orderLabel->getCourierService();
            $data['deliveryInstructions'] = $orderLabel->getDeliveryInstructions();
            $data['signature'] = $orderLabel->getSignature();
            $data['parcels'] = $orderLabel->getParcels();
            $data['insurance'] = $orderLabel->getInsurance();
            $data['insuranceMonetary'] = $orderLabel->getInsuranceMonetary();
            $request->setData($data);
        } catch (NotFound $e) {
            // New label so there won't be a previously set shippingAccountId
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['channelName']);
        unset($data['courierName']);
        unset($data['courierService']);
        unset($data['deliveryInstructions']);
        unset($data['signature']);
        unset($data['parcels']);
        unset($data['insurance']);
        unset($data['insuranceMonetary']);
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

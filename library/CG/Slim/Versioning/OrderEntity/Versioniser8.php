<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Order\Shared\Entity as Order;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser8 implements VersioniserInterface
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
        if (!isset($data['id']) || isset($data['archived'])) {
            return;
        }

        try {
            /** @var Order $order */
            $order = $this->service->fetch($data['id']);
            $data['archived'] = $order->getArchived();
        } catch (NotFound $e) {
            // New order - assume we don't want it to be archived
            $data['archived'] = false;
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        // Archive still needs to be included
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

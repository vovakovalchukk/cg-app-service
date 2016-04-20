<?php
namespace CG\Slim\Versioning\TrackingEntity;

use CG\Order\Service\Tracking\Service;
use CG\Order\Shared\Tracking\Status;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
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
        if (isset($data['status'])) {
            return;
        }
        if (!isset($data['id'], $data['orderId'])) {
            $data['status'] = Status::PENDING;
            $request->setData($data);
            return;
        }

        try {
            $tracking = $this->service->fetch($data['id'], $data['orderId']);
            if (!$tracking->getStatus()) {
                return;
            }
            $data['status'] = $tracking->getStatus();
            $request->setData($data);
        } catch (NotFound $e) {
            // New entity so there won't be any previously set data
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['status']);
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

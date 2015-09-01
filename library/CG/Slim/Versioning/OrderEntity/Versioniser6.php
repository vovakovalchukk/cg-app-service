<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser6 implements VersioniserInterface
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
        if (!isset($data['id']) || isset($data['externalUsername'])) {
            return;
        }

        try {
            $order = $this->service->fetch($data['id']);
            $data['externalUsername'] = $order->getExternalUsername();
            $request->setData($data);
        } catch (NotFound $e) {
            // New order so there won't be a previously set externalUsername
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['externalUsername']);
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

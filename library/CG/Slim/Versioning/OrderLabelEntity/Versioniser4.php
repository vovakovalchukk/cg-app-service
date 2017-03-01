<?php
namespace CG\Slim\Versioning\OrderLabelEntity;

use CG\Order\Service\Label\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser4 implements VersioniserInterface
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
        if (!isset($data['id']) || isset($data['mongoId'])) {
            return;
        }

        try {
            $orderLabel = $this->service->fetch($data['id']);
            $data['mongoId'] = $orderLabel->getMongoId();
            $request->setData($data);
        } catch (NotFound $e) {
            // New label so there won't be a previously set mongoId
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['mongoId']);
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

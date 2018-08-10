<?php
namespace CG\Slim\Versioning\OrderLabelEntity;

use CG\Order\Service\Label\Service;
use CG\Order\Shared\Label\Entity;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser5 implements VersioniserInterface
{
    /** @var Service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['id']) || isset($data['costPrice'], $data['costCurrencyCode'])) {
            return;
        }

        try {
            /** @var Entity $orderLabel */
            $orderLabel = $this->service->fetch($data['id']);
            $data['costPrice'] = $orderLabel->getCostPrice();
            $data['costCurrencyCode'] = $orderLabel->getCostCurrencyCode();
            $request->setData($data);
        } catch (NotFound $e) {
            // New label so there won't be a previously set values
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['costPrice'], $data['costCurrencyCode']);
        $response->setData($data);
    }
} 

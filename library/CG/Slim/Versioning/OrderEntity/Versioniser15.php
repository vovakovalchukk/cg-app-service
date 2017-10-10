<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser15 implements VersioniserInterface
{
    /** @var Service $service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['id']) || isset($data['exchangeRate'], $data['exchangeRateCurrencyCode'])) {
            return;
        }

        try {
            /** @var \CG\Order\Shared\Entity $order */
            $order = $this->service->fetch($data['id']);
            $data['exchangeRate'] = $data['exchangeRate'] ?? $order->getExchangeRate();
            $data['exchangeRateCurrencyCode'] = $data['exchangeRateCurrencyCode'] ?? $order->getExchangeRateCurrencyCode();
        } catch (NotFound $e) {
            // New order so there won't be a previously set value
            $data['exchangeRate'] = null;
            $data['exchangeRateCurrencyCode'] = null;
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['exchangeRate'], $data['exchangeRateCurrencyCode']);
        $response->setData($data);
    }
}

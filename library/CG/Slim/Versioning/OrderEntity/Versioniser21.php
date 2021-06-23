<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Order\Shared\Address\Redacted as AddressRedacted;
use CG\Order\Shared\AddressInterface as Address;
use CG\Order\Shared\Entity as Order;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser21 implements VersioniserInterface
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
        $data['id'] = $data['id'] ?? $params['orderId'] ?? null;

        if (isset($data['iossNumber'])) {
            return;
        }
        if (!isset($data['id'])) {
            $data['iossNumber'] = null;
            $request->setData($data);
            return;
        }

        try {
            /** @var Order $order */
            $order = $this->service->fetch($data['id']);
            $data['iossNumber'] = $order->getIossNumber();
        } catch (NotFound $e) {
            // New order so there won't be a previously set value
            $data['iossNumber'] = null;
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['iossNumber']);
        $response->setData($data);
    }
}

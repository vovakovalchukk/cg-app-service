<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Order\Shared\Entity as Order;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser17 implements VersioniserInterface
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
        if (!isset($data['id']) || isset($data['dispatchable'])) {
            return;
        }

        try {
            /** @var Order $order */
            $order = $this->service->fetch($data['id']);
            $data['dispatchable'] = $order->getDispatchable();
        } catch (NotFound $e) {
            // New order so there won't be a previously set value
            $data['dispatchable'] = null;
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['dispatchable']);
        $response->setData($data);
    }
}

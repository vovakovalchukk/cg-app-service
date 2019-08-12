<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Order\Shared\Address\Redacted as AddressRedacted;
use CG\Order\Shared\AddressInterface as Address;
use CG\Order\Shared\Entity as Order;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser20 implements VersioniserInterface
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
        if (isset($data['linkable'])) {
            return;
        }
        if (!isset($data['id'])) {
            $data['linkable'] = true;
            $request->setData($data);
            return;
        }

        try {
            /** @var Order $order */
            $order = $this->service->fetch($data['id']);
            $data['linkable'] = $order->isLinkable();
        } catch (NotFound $e) {
            // New order so there won't be a previously set value
            $data['linkable'] = true;
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['linkable']);
        $response->setData($data);
    }
}

<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Order\Shared\Address\Redacted as AddressRedacted;
use CG\Order\Shared\AddressInterface as Address;
use CG\Order\Shared\Entity as Order;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser19 implements VersioniserInterface
{
    protected const REDACTED = 'REDACTED';

    /** @var Service $service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();

        try {
            if (!isset($data['id'])) {
                throw new NotFound();
            }
            /** @var Order $order */
            $order = $this->service->fetch($data['id']);
        } catch (NotFound $e) {
            // New order so there won't be a previously set value
            $order = null;
        }

        $data['buyerMessageRedacted'] = $data['buyerMessageRedacted'] ?? ($order ? $order->getBuyerMessageRedacted() : false);
        if (isset($data['buyerMessage'])) {
            $data['buyerMessage'] = $data['buyerMessage'] === static::REDACTED ? null : $data['buyerMessage'];
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        if (isset($data['buyerMessageRedacted'])) {
            $data['buyerMessage'] = $data['buyerMessageRedacted'] ? static::REDACTED : ($data['buyerMessage'] ?? null);
            unset($data['buyerMessageRedacted']);
        }
        $response->setData($data);
    }
}

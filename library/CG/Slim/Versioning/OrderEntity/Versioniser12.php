<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser12 implements VersioniserInterface
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
        if (!isset($data['id']) || isset($data['recipientVatNumber'])) {
            return;
        }
        $data['recipientVatNumber'] = null;

        try {
            /** @var \CG\Order\Shared\Entity $order */
            $order = $this->service->fetch($data['id']);
            $data['recipientVatNumber'] = $order->getRecipientVatNumber();
        } catch (NotFound $e) {
            // New order so there won't be a previously set value
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['recipientVatNumber']);
        $response->setData($data);
    }
}

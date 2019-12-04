<?php
namespace CG\Slim\Versioning\OrderItemEntity;

use CG\Order\Service\Item\Service as Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser12 implements VersioniserInterface
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
        if (isset($data['supplierId'])) {
            return;
        }
        $data['supplierId'] = null;
        if (isset($data['id'])) {
            try {
                $item = $this->service->fetch($data['id']);
                $data['supplierId'] = $item->getSupplierId();
            } catch (NotFound $e) {
                // No-op, no existing item to copy from
            }
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['supplierId']);
        $response->setData($data);
    }
}
<?php
namespace CG\Slim\Versioning\TrackingEntity;

use CG\Order\Service\Tracking\Service;
use CG\Order\Shared\Tracking\Status;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser2 implements VersioniserInterface
{
    /**
     * @var Service $service
     */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['packageNumber'])) {
            return;
        }
        if (!isset($data['id'], $data['orderId'])) {
            return;
        }
        try {
            $tracking = $this->service->fetch($data['id'], $data['orderId']);
            if (!$tracking->getPackageNumber()) {
                return;
            }
            $data['packageNumber'] = $tracking->getPackageNumber();
            $request->setData($data);
        } catch (NotFound $e) {
            // New entity so there won't be any previously set data
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['packageNumber']);
        $response->setData($data);
    }
}

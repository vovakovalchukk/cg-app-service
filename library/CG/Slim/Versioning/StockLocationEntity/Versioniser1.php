<?php
namespace CG\Slim\Versioning\StockLocationEntity;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Stock\Location\Service;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
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
        if (!isset($data['id']) || isset($data['onPurchaseOrder'])) {
            return;
        }

        try {
            /** @var StockLocation $stockLocation */
            $stockLocation = $this->service->fetch($data['id']);
            $data['onPurchaseOrder'] = $stockLocation->getOnPurchaseOrder();
            $request->setData($data);
        } catch (NotFound $exception) {
            // New entity so there won't be a previously set value
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['onPurchaseOrder']);
        $response->setData($data);
    }
}

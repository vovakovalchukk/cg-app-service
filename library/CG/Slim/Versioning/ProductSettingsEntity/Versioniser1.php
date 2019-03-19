<?php
namespace CG\Slim\Versioning\ProductSettingsEntity;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Settings\Product\Service;
use CG\Settings\Product\Entity as ProductSetting;
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
        if (!isset($data['id']) || isset($data['includePurchaseOrdersInAvailable'])) {
            return;
        }

        try {
            /** @var ProductSetting $productSetting */
            $productSetting = $this->service->fetch($data['id']);
            $data['includePurchaseOrdersInAvailable'] = $productSetting->isIncludePurchaseOrdersInAvailable();
            $request->setData($data);
        } catch (NotFound $exception) {
            // New setting so there won't be a previously set value
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['includePurchaseOrdersInAvailable']);
        $response->setData($data);
    }
}

<?php
namespace CG\Slim\Versioning\ProductSettingsEntity;

use CG\Settings\Product\Service as SettingsProductService;
use CG\Settings\Product\Entity as ProductSettings;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser2 implements VersioniserInterface
{
    /** @var  SettingsProductService */
    protected $service;

    public function __construct(SettingsProductService $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['lowStockThresholdOn'], $data['lowStockThresholdValue']) || !($params['id'] ?? null)) {
            return;
        }

        try {
            /** @var ProductSettings $productSettings */
            $productSettings = $this->service->fetch($params['id']);
            $data['lowStockThresholdOn'] = $productSettings->isLowStockThresholdOn();
            $data['lowStockThresholdValue'] = $productSettings->getLowStockThresholdValue();
            $request->setData($data);
        } catch (NotFound $e) {
            // Entity not found so no information to copy
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['lowStockThresholdOn'], $data['lowStockThresholdValue']);
        $response->setData($data);
    }
}

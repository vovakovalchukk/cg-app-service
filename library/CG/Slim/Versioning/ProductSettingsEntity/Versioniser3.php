<?php
namespace CG\Slim\Versioning\ProductSettingsEntity;

use CG\Settings\Product\Service as SettingsProductService;
use CG\Settings\Product\Entity as ProductSettings;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser3 implements VersioniserInterface
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
        if (isset($data['reorderQuantity']) || !($params['id'] ?? null)) {
            return;
        }

        try {
            /** @var ProductSettings $productSettings */
            $productSettings = $this->service->fetch($params['id']);
            $data['reorderQuantity'] = $productSettings->getReorderQuantity();
            $request->setData($data);
        } catch (NotFound $e) {
            // Entity not found so no information to copy
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['reorderQuantity']);
        $response->setData($data);
    }
}

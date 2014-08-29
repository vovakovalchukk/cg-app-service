<?php
namespace CG\Slim\Versioning\AliasSettingsEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['accountId']) || !isset($data['shippingService'])) {
            $data['accountId'] = null;
            $data['shippingService'] = null;
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['accountId']);
        unset($data['shippingService']);
        $response->setData($data);
    }
}
 
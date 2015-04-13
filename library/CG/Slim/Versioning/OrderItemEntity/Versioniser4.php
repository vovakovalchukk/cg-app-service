<?php
namespace CG\Slim\Versioning\OrderItemEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser4 implements VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['isStockManaged'])) {
            $data['isStockManaged'] = true;
        }
        if (!isset($data['isStockManaged'])) {
            $data['isExternallyFulfilled'] = null;
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['isStockManaged']);
        unset($data['isExternallyFulfilled']);
        $response->setData($data);
    }
}



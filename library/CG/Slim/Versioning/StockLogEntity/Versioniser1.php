<?php
namespace CG\Slim\Versioning\StockLogEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request)
    {
        // We don't support saving entities
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['referenceSku'], $data['adjustmentReferenceQuantity']);
        $response->setData($data);
    }
}

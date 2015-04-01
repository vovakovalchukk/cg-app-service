<?php
namespace CG\Slim\Versioning\UnimportedListingEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser3 implements VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['variationSkus'])) {
            $data['variationSkus'] = [];
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['variationSkus']);
        $response->setData($data);
    }
}

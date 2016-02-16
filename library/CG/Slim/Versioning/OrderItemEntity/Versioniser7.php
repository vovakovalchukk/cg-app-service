<?php
namespace CG\Slim\Versioning\OrderItemEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser7 implements VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['cgCreationDate'])) {
            $data['cgCreationDate'] = null;
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['cgCreationDate']);
        $response->setData($data);
    }
}



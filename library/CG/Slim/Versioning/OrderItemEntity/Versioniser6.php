<?php
namespace CG\Slim\Versioning\OrderItemEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser6 implements VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['lastUpdateFromChannel'])) {
            $data['lastUpdateFromChannel'] = null;
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['lastUpdateFromChannel']);
        $response->setData($data);
    }
}



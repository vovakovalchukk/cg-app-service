<?php
namespace CG\Slim\Versioning\InvoiceMappingEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        $data['emailSubject'] = $data['emailSubject'] ?? null;
        $data['emailTemplate'] = $data['emailTemplate'] ?? null;
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['emailSubject']);
        unset($data['emailTemplate']);
        $response->setData($data);
    }
}

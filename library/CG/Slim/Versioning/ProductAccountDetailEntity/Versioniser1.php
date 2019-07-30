<?php
namespace CG\Slim\Versioning\ProductAccountDetailEntity;

use CG\Product\AccountDetail\ExternalInterface;
use CG\Product\AccountDetail\Service;
use CG\Slim\Versioning\VersioniserInterface;
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
        if (!isset($data['id']) || isset($data['externalType'], $data['externalData'])) {
            return;
        }

        try {
            /** @var ExternalInterface $externalData */
            $externalData = $this->service->fetch($data['id'])->getExternal();
            $data['externalType'] = $data['externalType'] ?? $externalData::type();
            $data['externalData'] = $data['externalData'] ?? $externalData->toArray();
        } catch (NotFound $exception) {
            // New entity
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['externalType'], $data['externalData']);
        return $data;
    }
}
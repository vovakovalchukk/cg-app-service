<?php
namespace CG\Slim\Versioning\ProductSettingsCollection;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Slim\Versioning\ProductSettingsEntity\Versioniser1 as EntityVersioniser1;

class Versioniser1 implements VersioniserInterface
{
    protected $entityVersioner;

    public function __construct(EntityVersioniser1 $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->getEntityVersioner()->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['product'])) {
            return $this->getEntityVersioner()->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['product'] as $product) {
            $currentVersion = $this->getEntityVersioner()->downgradeResponse($params, $product, $requestedVersion);
        }
        return $currentVersion;
    }

    public function setEntityVersioner(EntityVersioniser1 $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
        return $this;
    }

    public function getEntityVersioner()
    {
        return $this->entityVersioner;
    }
}
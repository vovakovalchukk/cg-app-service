<?php
namespace CG\Slim\Versioning\OrderCollection;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Slim\Versioning\OrderEntity\Versioniser2 as EntityVersioniser2;

class Versioniser2 implements VersioniserInterface
{
    protected $entityVersioner;

    public function __construct(EntityVersioniser2 $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }

    public function setEntityVersioner(EntityVersioniser2 $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
        return $this;
    }

    /**
     * @return EntityVersioniser2
     */
    public function getEntityVersioner()
    {
        return $this->entityVersioner;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->getEntityVersioner()->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['order'])) {
            return $this->getEntityVersioner()->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['order'] as $order) {
            $currentVersion = $this->getEntityVersioner()->downgradeResponse($params, $order, $requestedVersion);
        }
        return $currentVersion;
    }
}

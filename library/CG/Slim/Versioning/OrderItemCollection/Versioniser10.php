<?php
namespace CG\Slim\Versioning\OrderItemCollection;

use CG\Slim\Versioning\OrderItemEntity\Versioniser10 as EntityVersioniser;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser10 implements VersioniserInterface
{
    /** @var EntityVersioniser $entityVersioner */
    protected $entityVersioner;

    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->entityVersioner->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['item'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['item'] as $item) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $item, $requestedVersion);
        }
        return $currentVersion;
    }

    /**
     * @return self
     */
    public function setEntityVersioner(EntityVersioniser $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
        return $this;
    }
}

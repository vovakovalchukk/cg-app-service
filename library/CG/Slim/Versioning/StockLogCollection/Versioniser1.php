<?php
namespace CG\Slim\Versioning\StockLogCollection;

use CG\Slim\Versioning\StockLogEntity\Versioniser1 as EntityVersioniser;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /** @var VersioniserInterface $entityVersioner */
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
        if (!isset($resources['stockLog'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['stockLog'] as $stockLog) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $stockLog, $requestedVersion);
        }
        return $currentVersion;
    }

    /**
     * @return self
     */
    public function setEntityVersioner(VersioniserInterface $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
        return $this;
    }
}

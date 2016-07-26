<?php
namespace CG\Slim\Versioning\StockCollection;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser implements VersioniserInterface
{
    /** @var VersioniserInterface $entityVersioner */
    protected $entityVersioner;

    public function __construct(VersioniserInterface $entityVersioner)
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
        if (!isset($resources['stock'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['stock'] as $stock) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $stock, $requestedVersion);
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

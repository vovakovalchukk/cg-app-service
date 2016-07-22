<?php
namespace CG\Slim\Versioning\ProductCollection;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser6 implements VersioniserInterface
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
        if (!isset($resources['product'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['product'] as $product) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $product, $requestedVersion);
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

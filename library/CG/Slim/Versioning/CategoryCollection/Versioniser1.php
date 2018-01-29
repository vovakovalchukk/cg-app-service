<?php
namespace CG\Slim\Versioning\CategoryCollection;

use CG\Slim\Versioning\CategoryEntity\Versioniser1 as EntityVersioner1;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /** @var EntityVersioner1 */
    protected $entityVersioner;

    public function __construct(EntityVersioner1 $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->entityVersioner->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['category'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['category'] as $category) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $category, $requestedVersion);
        }
        return $currentVersion;
    }
} 
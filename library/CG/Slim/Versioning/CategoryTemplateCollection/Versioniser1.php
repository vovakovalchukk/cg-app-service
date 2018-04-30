<?php
namespace CG\Slim\Versioning\CategoryTemplateCollection;

use CG\Slim\Versioning\CategoryTemplateEntity\Versioniser1 as EntityVersioner1;
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
        if (!isset($resources['categoryTemplate'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['categoryTemplate'] as $categoryTemplate) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $categoryTemplate, $requestedVersion);
        }
        return $currentVersion;
    }
}

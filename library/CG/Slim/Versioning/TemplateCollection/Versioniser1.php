<?php
namespace CG\Slim\Versioning\TemplateCollection;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Slim\Versioning\TemplateEntity\Versioniser1 as EntityVersioniser1;

class Versioniser1 implements VersioniserInterface
{
    protected $entityVersioner;

    public function __construct(VersioniserInterface $entityVersioner)
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
        if (!isset($resources['template'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['template'] as $item) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $item, $requestedVersion);
        }
        return $currentVersion;
    }
}
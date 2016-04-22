<?php
namespace CG\Slim\Versioning\AliasSettingsCollection;

use CG\Slim\Versioning\AliasSettingsEntity\Versioniser2 as EntityVersioniser;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser2 implements VersioniserInterface
{
    protected $entityVersioniser;

    public function __construct(EntityVersioniser $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->getEntityVersioniser()->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['alias'])) {
            return $this->getEntityVersioniser()->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['alias'] as $aliasResponse) {
            $currentVersion = $this->getEntityVersioniser()->downgradeResponse($params, $aliasResponse, $requestedVersion);
        }
        return $currentVersion;
    }

    protected function getEntityVersioniser()
    {
        return $this->entityVersioniser;
    }

    protected function setEntityVersioniser(EntityVersioniser $entityVersioniser)
    {
        $this->entityVersioniser = $entityVersioniser;
        return $this;
    }
}
 
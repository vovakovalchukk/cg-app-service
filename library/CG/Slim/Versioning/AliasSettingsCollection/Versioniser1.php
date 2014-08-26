<?php
namespace CG\Slim\Versioning\AliasSettingsCollection;

use CG\Slim\Versioning\AliasSettingsEntity\Versioniser1 as AliasVersioniser1;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    protected $aliasVersioniser1;

    public function __construct(AliasVersioniser1 $aliasVersioniser1)
    {
        $this->setAliasVersioniser1($aliasVersioniser1);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->getAliasVersioniser1()->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['alias'])) {
            return $this->getAliasVersioniser1()->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['alias'] as $aliasResponse) {
            $currentVersion = $this->getAliasVersioniser1()->downgradeResponse($params,
                $aliasResponse, $requestedVersion);
        }
        return $currentVersion;
    }

    protected function getAliasVersioniser1()
    {
        return $this->aliasVersioniser1;
    }

    protected function setAliasVersioniser1(AliasVersioniser1 $aliasVersioniser1)
    {
        $this->aliasVersioniser1 = $aliasVersioniser1;
        return $this;
    }
}
 
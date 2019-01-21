<?php
namespace CG\Slim\Versioning\ProductSettingsCollection;

use CG\Slim\Versioning\ProductSettingsEntity\Versioniser1 as EntityVersioniser;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /** @var EntityVersioniser */
    protected $entityVersioniser;

    public function __construct(EntityVersioniser $entityVersioniser)
    {
        $this->entityVersioniser = $entityVersioniser;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->entityVersioniser->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['productSettings'])) {
            return $this->entityVersioniser->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['productSettings'] as $productSetting) {
            $currentVersion = $this->entityVersioniser->downgradeResponse($params, $productSetting, $requestedVersion);
        }
        return $currentVersion;
    }
}

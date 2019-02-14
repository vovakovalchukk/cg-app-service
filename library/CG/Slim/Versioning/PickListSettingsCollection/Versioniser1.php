<?php
namespace CG\Slim\Versioning\PickListSettingsCollection;

use CG\Slim\Versioning\PickListSettingsCollection\Versioniser1 as PickListVersioniser1;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /** @var VersioniserInterface */
    protected $pickListVersioniser;

    public function __construct(PickListVersioniser1 $pickListVersioniser)
    {
        $this->setPickListVersioniser($pickListVersioniser);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->pickListVersioniser->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['pickList'])) {
            return $this->pickListVersioniser->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['pickList'] as $pickList) {
            $currentVersion = $this->pickListVersioniser->downgradeResponse(
                $params,
                $pickList,
                $requestedVersion
            );
        }
        return $currentVersion;
    }

    protected function setPickListVersioniser(VersioniserInterface $pickListVersioniser)
    {
        $this->pickListVersioniser = $pickListVersioniser;
        return $this;
    }
}
 
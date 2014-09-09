<?php
namespace CG\Slim\Versioning\UnimportedListingCollection;

use CG\Slim\Versioning\UnimportedListingEntity\Versioniser1 as UnimportedListingVersioner1;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    protected $unimportedListingVersioner;

    public function __construct(UnimportedListingVersioner1 $entityVersioner)
    {
        $this->setUnimportedListingVersioner($entityVersioner);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->getUnimportedListingVersioner()->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['unimportedListing'])) {
            return $this->getUnimportedListingVersioner()->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['unimportedListing'] as $unimportedListingResponse) {
            $currentVersion = $this->getUnimportedListingVersioner()->downgradeResponse(
                $params,
                $unimportedListingResponse,
                $requestedVersion
            );
        }
        return $currentVersion;
    }

    /**
     * @return VersioniserInterface
     */
    public function getUnimportedListingVersioner()
    {
        return $this->unimportedListingVersioner;
    }

    public function setUnimportedListingVersioner(VersioniserInterface $unimportedListingVersioner)
    {
        $this->unimportedListingVersioner = $unimportedListingVersioner;
        return $this;
    }
}

<?php
namespace CG\Slim\Versioning\UnimportedListingCollection;

use CG\Slim\Versioning\UnimportedListingEntity\Versioniser4 as UnimportedListingVersioner4;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser4 implements VersioniserInterface
{
    /** @var UnimportedListingVersioner4 $unimportedListingVersioner */
    protected $unimportedListingVersioner;

    public function __construct(UnimportedListingVersioner4 $unimportedListingVersioner)
    {
        $this->setUnimportedListingVersioner($unimportedListingVersioner);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->unimportedListingVersioner->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['unimportedListing'])) {
            return $this->unimportedListingVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['unimportedListing'] as $unimportedListingResponse) {
            $currentVersion = $this->unimportedListingVersioner->downgradeResponse(
                $params,
                $unimportedListingResponse,
                $requestedVersion
            );
        }
        return $currentVersion;
    }

    /**
     * @return self
     */
    public function setUnimportedListingVersioner(UnimportedListingVersioner4 $unimportedListingVersioner)
    {
        $this->unimportedListingVersioner = $unimportedListingVersioner;
        return $this;
    }
}

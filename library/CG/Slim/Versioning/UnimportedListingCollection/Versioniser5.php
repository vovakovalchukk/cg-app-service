<?php
namespace CG\Slim\Versioning\UnimportedListingCollection;

use CG\Slim\Versioning\UnimportedListingEntity\Versioniser5 as UnimportedListingVersioner5;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser5 implements VersioniserInterface
{
    /** @var UnimportedListingVersioner5 $unimportedListingVersioner */
    protected $unimportedListingVersioner;

    public function __construct(UnimportedListingVersioner5 $unimportedListingVersioner)
    {
        $this->unimportedListingVersioner = $unimportedListingVersioner;
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
}

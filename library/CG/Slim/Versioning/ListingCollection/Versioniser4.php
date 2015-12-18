<?php
namespace CG\Slim\Versioning\ListingCollection;

use CG\Slim\Versioning\ListingEntity\Versioniser4 as ListingVersioniser4;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser4 implements VersioniserInterface
{
    /** @var ListingVersioniser4 $listingVersioniser4 */
    protected $listingVersioniser;

    public function __construct(ListingVersioniser4 $listingVersioniser)
    {
        $this->setListingVersioniser($listingVersioniser);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->listingVersioniser->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['listing'])) {
            return $this->listingVersioniser->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['listing'] as $listingResponse) {
            $currentVersion = $this->listingVersioniser->downgradeResponse(
                $params,
                $listingResponse,
                $requestedVersion
            );
        }

        return $currentVersion;
    }

    /**
     * @return self
     */
    protected function setListingVersioniser(ListingVersioniser4 $listingVersioniser)
    {
        $this->listingVersioniser = $listingVersioniser;
        return $this;
    }
}

<?php
namespace CG\Slim\Versioning\ListingCollection;

use CG\Slim\Versioning\ListingEntity\Versioniser2 as ListingVersioniser2;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser2 implements VersioniserInterface
{
    protected $listingVersioniser2;

    public function __construct(ListingVersioniser2 $listingVersioniser2)
    {
        $this->setListingVersioniser2($listingVersioniser2);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->getListingVersioniser2()->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['listing'])) {
            return $this->getListingVersioniser2()->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['listing'] as $listingResponse) {
            $currentVersion = $this->getListingVersioniser2()->downgradeResponse($params,
                $listingResponse, $requestedVersion);
        }
        return $currentVersion;
    }

    protected function getListingVersioniser2()
    {
        return $this->listingVersioniser2;
    }

    protected function setListingVersioniser2(ListingVersioniser2 $listingVersioniser2)
    {
        $this->listingVersioniser2 = $listingVersioniser2;
        return $this;
    }
}

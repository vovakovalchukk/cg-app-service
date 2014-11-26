<?php
namespace CG\Slim\Versioning\ListingCollection;

use CG\Slim\Versioning\ListingEntity\Versioniser1 as ListingVersioniser1;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    protected $listingVersioniser1;

    public function __construct(ListingVersioniser1 $listingVersioniser1)
    {
        $this->setListingVersioniser1($listingVersioniser1);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->getListingVersioniser1()->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['listing'])) {
            return $this->getListingVersioniser1()->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['listing'] as $listingResponse) {
            $currentVersion = $this->getListingVersioniser1()->downgradeResponse($params,
                $listingResponse, $requestedVersion);
        }
        return $currentVersion;
    }

    protected function getListingVersioniser1()
    {
        return $this->listingVersioniser1;
    }

    protected function setListingVersioniser1(ListingVersioniser1 $listingVersioniser1)
    {
        $this->listingVersioniser1 = $listingVersioniser1;
        return $this;
    }
}

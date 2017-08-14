<?php
namespace CG\Slim\Versioning\ListingCollection;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser implements VersioniserInterface
{
    /** @var VersioniserInterface $entityVersioniser */
    protected $entityVersioniser;

    public function __construct(VersioniserInterface $entityVersioniser)
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
        if (!isset($resources['listing'])) {
            return $response;
        }

        foreach ($resources['listing'] as $listing) {
            $this->entityVersioniser->downgradeResponse(
                $params,
                $listing,
                $requestedVersion
            );
        }
    }
}

<?php
namespace CG\Slim\Versioning\ListingStatusHistoryCollection;

use CG\Slim\Versioning\ListingStatusHistoryEntity\Versioniser1 as EntityVersioniser;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /** @var VersioniserInterface $entityVersioniser */
    protected $entityVersioniser;

    public function __construct(EntityVersioniser $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->entityVersioniser->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['listingStatusHistory'])) {
            return $this->entityVersioniser->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['listingStatusHistory'] as $listingStatusHistoryResponse) {
            $currentVersion = $this->entityVersioniser->downgradeResponse(
                $params,
                $listingStatusHistoryResponse,
                $requestedVersion
            );
        }
        return $currentVersion;
    }

    /**
     * @return self
     */
    protected function setEntityVersioniser(VersioniserInterface $entityVersioniser)
    {
        $this->entityVersioniser = $entityVersioniser;
        return $this;
    }
} 

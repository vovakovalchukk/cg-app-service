<?php
namespace CG\Slim\Versioning\ListingCollection;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Product\Service\Service;
use CG\Slim\Versioning\ListingEntity\Versioniser1 as EntityVersioniser;

class Versioniser1 implements VersioniserInterface
{
    public function __construct(EntityVersioniser $entityVersioniser)
    {
        $this->setEntityVersioniser($entityVersioniser);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->getEntityVersioniser()->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['listing'])) {
            return $resources;
        }

        foreach ($resources['listing'] as $listing) {
            $this->getEntityVersioniser()->downgradeResponse(
                $params,
                $listing,
                $requestedVersion
            );
        }
    }

    /**
     * @return self
     */
    public function setEntityVersioniser(EntityVersioniser $entityVersioniser)
    {
        $this->entityVersioniser = $entityVersioniser;
        return $this;
    }

    /**
     * @return EntityVersioniser
     */
    protected function getEntityVersioniser()
    {
        return $this->entityVersioniser;
    }
}

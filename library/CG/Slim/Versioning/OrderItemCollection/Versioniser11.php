<?php
namespace CG\Slim\Versioning\OrderItemCollection;

<<<<<<< HEAD
use CG\Slim\Versioning\VersioniserInterface;
use CG\Slim\Versioning\OrderItemEntity\Versioniser10 as EntityVersioniser;
use CG\Slim\Versioning\OrderItemCollection\Versioniser1 as Versioniser1;

class Versioniser10 extends Versioniser1 implements VersioniserInterface
{
=======
use CG\Slim\Versioning\OrderItemEntity\Versioniser10 as EntityVersioniser;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser10 implements VersioniserInterface
{
    /** @var EntityVersioniser $entityVersioner */
    protected $entityVersioner;

>>>>>>> 55707f115aeeaa04b9a9f18164667817c43c9af5
    public function __construct(EntityVersioniser $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }
<<<<<<< HEAD
}
=======

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->entityVersioner->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['item'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['item'] as $item) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $item, $requestedVersion);
        }
        return $currentVersion;
    }

    /**
     * @return self
     */
    public function setEntityVersioner(EntityVersioniser $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
        return $this;
    }
}
>>>>>>> 55707f115aeeaa04b9a9f18164667817c43c9af5

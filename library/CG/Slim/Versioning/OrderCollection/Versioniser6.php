<?php
namespace CG\Slim\Versioning\OrderCollection;

use CG\Slim\Versioning\OrderEntity\Versioniser6 as EntityVersioniser6;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser6 implements VersioniserInterface
{
    /**
     * @var EntityVersioniser6 $entityVersioner
     */
    protected $entityVersioner;

    public function __construct(EntityVersioniser6 $entityVersioner)
    {
        $this->setEntityVersioner($entityVersioner);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->entityVersioner->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['order'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['order'] as $order) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $order, $requestedVersion);
        }
        return $currentVersion;
    }

    /**
     * @return self
     */
    protected function setEntityVersioner($entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
        return $this;
    }
} 

<?php
namespace CG\Slim\Versioning\OrderLabelCollection;

use CG\Slim\Versioning\OrderLabelEntity\Versioniser1 as EntityVersioniser1;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /**
     * @var EntityVersioniser1 $entityVersioner
     */
    protected $entityVersioner;

    public function __construct(EntityVersioniser1 $entityVersioner)
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
        if (!isset($resources['orderLabel'])) {
            return $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
        }

        $currentVersion = null;
        foreach ($resources['orderLabel'] as $orderLabel) {
            $currentVersion = $this->entityVersioner->downgradeResponse($params, $orderLabel, $requestedVersion);
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

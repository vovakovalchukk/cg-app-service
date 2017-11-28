<?php
namespace CG\Slim\Versioning\ProductDetailCollection;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Slim\Versioning\ProductDetailEntity\Versioniser1 as EntityVersioniser1;

class Versioniser1 implements VersioniserInterface
{
    protected $entityVersioner;

    public function __construct(EntityVersioniser1 $entityVersioner)
    {
        $this->entityVersioner = $entityVersioner;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $this->entityVersioner->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['productDetail'])) {
            $this->entityVersioner->downgradeResponse($params, $response, $requestedVersion);
            return;
        }

        $currentVersion = null;
        foreach ($resources['productDetail'] as $product) {
            $this->entityVersioner->downgradeResponse($params, $product, $requestedVersion);
        }
    }
}
<?php
namespace CG\Slim\Versioning\ProductChannelDetailCollection;

use CG\Product\ChannelDetail\Mapper;
use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

abstract class AbstractVersioniser
{
    /** @var VersioniserInterface $entityVersioniser */
    protected $entityVersioniser;

    public function upgradeRequest(array $params, Hal $request)
    {
        return $this->entityVersioniser->upgradeRequest($params, $request);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        foreach ($resources[Mapper::RESOURCE] ?? [] as $productChannelDetail) {
            $this->entityVersioniser->downgradeResponse(
                $params,
                $productChannelDetail,
                $requestedVersion
            );
        }
    }
}
<?php
namespace CG\Slim\Versioning\GiftWrapCollection;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

abstract class AbstractVersioniser implements VersioniserInterface
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
        if (!isset($resources['giftWrap'])) {
            return $response;
        }

        foreach ($resources['giftWrap'] as $giftWrap) {
            $this->entityVersioniser->downgradeResponse(
                $params,
                $giftWrap,
                $requestedVersion
            );
        }
    }
}
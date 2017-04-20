<?php
namespace CG\Slim\Versioning\UnimportedListingMarketplace;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request)
    {
        // NoOp - endpoint doesn't support PUT requests
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $resources = $response->getResources();
        if (!isset($resources['unimportedListingMarketplace'])) {
            return 1;
        }

        /** @var Hal $unimportedListingMarketplace */
        foreach ($resources['unimportedListingMarketplace'] as $unimportedListingMarketplace) {
            $data = $unimportedListingMarketplace->getData();
            unset($data['accountId']);
            $unimportedListingMarketplace->setData($data);
        }

        return 1;
    }
}

<?php
namespace CG\Slim\Versioning\ListingEntity;

use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser3 extends AbstractVersioniser
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!array_key_exists('marketplace', $data)) {
            try {
                $entity = $this->fetchEntity($data);
                $data['marketplace'] = $entity->getMarketplace();
            } catch (NotFound $exception) {
                $data['marketplace'] = null;
            }
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['marketplace']);
        $response->setData($data);
    }
}

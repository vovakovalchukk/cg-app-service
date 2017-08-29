<?php
namespace CG\Slim\Versioning\ListingEntity;

use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser4 extends AbstractVersioniser
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['productSkus'])) {
            try {
                $entity = $this->fetchEntity($data);
                $data['productSkus'] = $entity->getProductSkus();
            } catch (NotFound $exception) {
                $data['productSkus'] = [];
            }
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['productSkus']);
        $response->setData($data);
    }
}

<?php
namespace CG\Slim\Versioning\ListingEntity;

use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser8 extends AbstractVersioniser
{
    public function upgradeRequest(array $params, Hal $request)
    {
        if (isset($data['name']) && isset($data['description']) && isset($data['price']) && isset($data['cost']) && isset($data['condition'])) {
            return;
        }

        $data = $request->getData();
        try {
            $entity = $this->fetchEntity($data);
            $data['lastModified'] = $entity->getLastModified();
        } catch (NotFound $exception) {
            $data['lastModified'] = null;
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['lastModified']);
        $response->setData($data);
    }
}

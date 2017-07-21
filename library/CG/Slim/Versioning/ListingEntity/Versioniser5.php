<?php
namespace CG\Slim\Versioning\ListingEntity;

use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser5 extends AbstractVersioniser
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!array_key_exists('replacedById', $data)) {
            try {
                $entity = $this->fetchEntity($data);
                $data['replacedById'] = $entity->getReplacedById();
            } catch (NotFound $exception) {
                $data['replacedById'] = null;
            }
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['replacedById']);
        $response->setData($data);
    }
}

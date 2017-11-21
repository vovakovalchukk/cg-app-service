<?php
namespace CG\Slim\Versioning\ListingEntity;

use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser7 extends AbstractVersioniser
{
    public function upgradeRequest(array $params, Hal $request)
    {
        if (isset($data['name']) && isset($data['description']) && isset($data['price']) && isset($data['cost']) && isset($data['condition'])) {
            return;
        }

        $data = $request->getData();
        var_dump($data);
        try {
            $entity = $this->fetchEntity($data);
            $data['name'] = $entity->getName();
            $data['description'] = $entity->getDescription();
            $data['price'] = $entity->getPrice();
            $data['cost'] = $entity->getCost();
            $data['condition'] = $entity->getCondition();
        } catch (NotFound $exception) {
            $data['name'] = $data['description'] = $data['price'] = $data['cost'] = $data['condition'] = null;
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['name'], $data['description'], $data['price'], $data['cost'], $data['condition']);
        $response->setData($data);
    }
}

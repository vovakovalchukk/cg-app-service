<?php
namespace CG\Slim\Versioning\ListingEntity;

use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser2 extends AbstractVersioniser
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!array_key_exists('url', $data)) {
            try {
                $entity = $this->fetchEntity($data);
                $data['url'] = $entity->getUrl();
            } catch (NotFound $exception) {
                $data['url'] = null;
            }
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['url']);
        $response->setData($data);
    }
}

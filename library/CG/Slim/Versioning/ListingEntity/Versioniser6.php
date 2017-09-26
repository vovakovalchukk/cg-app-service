<?php
namespace CG\Slim\Versioning\ListingEntity;

use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser6 extends AbstractVersioniser
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['skuExternalIdMap'])) {
            try {
                $entity = $this->fetchEntity($data);
                $data['skuExternalIdMap'] = $entity->getSkuExternalIdMap();
            } catch (NotFound $exception) {
                $data['skuExternalIdMap'] = [];
            }
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['skuExternalIdMap']);
        $response->setData($data);
    }
}

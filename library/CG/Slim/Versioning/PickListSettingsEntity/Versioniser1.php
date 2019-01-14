<?php
namespace CG\Slim\Versioning\PickListSettingsEntity;

use CG\Settings\PickList\Service;
use CG\Settings\PickList\Entity as PickList;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /** @var Service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['id']) || isset($data['locationNames'])) {
            return;
        }

        try {
            /** @var PickList $pickList */
            $pickList = $this->service->fetch($data['id']);
            $data['locationNames'] = $pickList->getLocationNames();
        } catch (NotFound $exception) {
            $data['locationNames'] = [];
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['locationNames']);
        $response->setData($data);
    }
}
 
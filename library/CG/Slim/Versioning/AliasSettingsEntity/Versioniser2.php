<?php
namespace CG\Slim\Versioning\AliasSettingsEntity;

use CG\Settings\Shipping\Alias\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser2 implements VersioniserInterface
{
    /** @var Service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['options']) || !isset($data['id'])) {
            return;
        }
        try {
            $alias = $this->service->fetch($data['id']);
            $data['options'] = $alias->getOptions();
            $request->setData($data);
        } catch (NotFound $e) {
            // No previous entity so no data to copy
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['options']);
        $response->setData($data);
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
}
 
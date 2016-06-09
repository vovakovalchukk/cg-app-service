<?php
namespace CG\Slim\Versioning\ProductEntity;

use CG\Product\Service\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser7 implements VersioniserInterface
{
    const OLD_DEFAULT = 'GB';

    /** @var Service $service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();

        if (isset($data['taxRateId'])) {
            $data['taxRateIds'][static::OLD_DEFAULT] = $data['taxRateId'];
            unset($data['taxRateId']);
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();

        $taxRateId = "";
        if (!empty($data['taxRateIds']) && isset($data['taxRateIds'][static::OLD_DEFAULT])) {
            $taxRateId = $data['taxRateIds'][static::OLD_DEFAULT];
        }

        $data['taxRateId'] = $taxRateId;
        unset($data['taxRateIds']);
        $response->setData($data);
    }

    /**
     * @return self
     */
    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
}

<?php
namespace CG\Slim\Versioning\OrderItemEntity;

use CG\Order\Service\Item\Service as Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser5 implements VersioniserInterface
{
    const DEFAULT_CALCULATED_TAX_PERCENTAGE = null;

    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['calculatedTaxPercentage'])) {
            $data = $this->setCalculatedTaxPercentageOnData($data);
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['calculatedTaxPercentage']);
        $response->setData($data);
    }

    protected function setCalculatedTaxPercentageOnData(array $data)
    {
        if (!isset($data["id"])) {
            $data["calculatedTaxPercentage"] = static::DEFAULT_CALCULATED_TAX_PERCENTAGE;
            return $data;
        }

        try {
            $item = $this->getService()->fetch($data["id"]);
            $data["calculatedTaxPercentage"] = $item->getCalculatedTaxPercentage();
            return $data;
        } catch (NotFound $e) {
            $data["calculatedTaxPercentage"] = static::DEFAULT_CALCULATED_TAX_PERCENTAGE;
            return $data;
        }
    }

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }
}



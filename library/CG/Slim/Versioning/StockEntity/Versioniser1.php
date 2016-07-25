<?php
namespace CG\Slim\Versioning\StockEntity;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Entity as Stock;
use CG\Stock\Service;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /** @var Service $service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (array_key_exists('stockMode', $data) && array_key_exists('stockLevel', $data)) {
            // We allow null values
            return;
        }

        try {
            if (!isset($data['id'])) {
                throw new NotFound('New entity');
            }

            /** @var Stock $stock */
            $stock = $this->service->fetch($data['id']);
            $data['stockMode'] = isset($data['stockMode']) ? $data['stockMode'] : $stock->getStockMode();
            $data['stockLevel'] = isset($data['stockLevel']) ? $data['stockLevel'] : $stock->getStockLevel();
        } catch (NotFound $exception) {
            // New entity - nothing to set
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['stockMode'], $data['stockLevel']);
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

<?php
namespace CG\Slim\Versioning\StockEntity;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Entity as Stock;
use CG\Stock\Service;
use Nocarrier\Hal;

class Versioniser2 implements VersioniserInterface
{
    /** @var Service $service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (array_key_exists('lowStockThresholdOn', $data)
            && array_key_exists('lowStockThresholdValue', $data)
            && array_key_exists('lowStockThresholdTriggered', $data)
        ) {
            // We allow null values
            return;
        }

        try {
            if (!isset($data['id'])) {
                throw new NotFound('New entity');
            }

            /** @var Stock $stock */
            $stock = $this->service->fetch($data['id']);
            $data['lowStockThresholdOn'] = $stock->isLowStockThresholdOn();
            $data['lowStockThresholdValue'] = $stock->getLowStockThresholdValue();
            $data['lowStockThresholdTriggered'] = $stock->isLowStockThresholdTriggered();
        } catch (NotFound $exception) {
            // New entity - nothing to set
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['lowStockThresholdOn'], $data['lowStockThresholdValue'], $data['lowStockThresholdTriggered']);
        $response->setData($data);
    }
}

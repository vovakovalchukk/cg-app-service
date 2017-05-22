<?php
namespace CG\Slim\Versioning\StockLocationEntity;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Entity as Stock;
use CG\Stock\Service as StockService;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /** @var StockService $stockService */
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['stockId']) || isset($data['organisationUnitId'], $data['sku'])) {
            return;
        }

        try {
            /** @var Stock $stock */
            $stock = $this->stockService->fetch($data['stockId']);
            if (!isset($data['organisationUnitId'])) {
                $data['organisationUnitId'] = $stock->getOrganisationUnitId();
            }
            if (!isset($data['sku'])) {
                $data['sku'] = $stock->getSku();
            }
            $request->setData($data);
        } catch (NotFound $exception) {
            // No stock entity to copy values from
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['organisationUnitId'], $data['sku']);
        $response->setData($data);
    }
}
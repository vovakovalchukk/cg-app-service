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
        if (!isset($data['id']) || isset($data['includePurchaseOrders'], $data['includePurchaseOrdersUseDefault'])) {
            return;
        }

        try {
            /** @var Stock $stock */
            $stock = $this->service->fetch($data['id']);
            $data['includePurchaseOrders'] = $data['includePurchaseOrders'] ?? $stock->isIncludePurchaseOrders();
            $data['includePurchaseOrdersUseDefault'] = $data['includePurchaseOrdersUseDefault'] ?? $stock->isIncludePurchasesOrderUseDefault();
        } catch (NotFound $exception) {
            // New entity - nothing to set
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['includePurchaseOrders'], $data['includePurchaseOrdersUseDefault']);
        $response->setData($data);
    }
}

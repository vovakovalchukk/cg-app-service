<?php
namespace CG\Slim\Versioning\OrderItemEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Service\Item\Service as Service;
use CG\Stdlib\Exception\Runtime\NotFound;

class Versioniser4 implements VersioniserInterface
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['isStockManaged'])) {
            $data = $this->setIsStockManagedOnData($data);
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['isStockManaged']);
        $response->setData($data);
    }

    protected function setIsStockManagedOnData(array $data)
    {
        if (!isset($data["id"])) {
            $data["isStockManaged"] = Item::DEFAULT_IS_STOCK_MANAGED;
            return $data;
        }

        try {
            $item = $this->getService()->fetch($data["id"]);
            $data["isStockManaged"] = $item->isStockManaged();
            return $data;
        } catch (NotFound $e) {
            $data["isStockManaged"] = Item::DEFAULT_IS_STOCK_MANAGED;
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



<?php
namespace CG\Slim\Versioning\OrderItemEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Order\Item\Entity as Item;

class Versioniser4 implements VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['isStockManaged'])) {
            $data['isStockManaged'] = Item::DEFAULT_IS_STOCK_MANAGED;
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['isStockManaged']);
        $response->setData($data);
    }
}



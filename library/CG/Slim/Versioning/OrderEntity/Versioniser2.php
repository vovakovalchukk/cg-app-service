<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Order\Shared\Entity as Order;

class Versioniser2 implements VersioniserInterface
{
    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['fulfilmentChannel'])) {
            $data['fulfilmentChannel'] = Order::DEFAULT_FULFILMENT_CHANNEL;
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['fulfilmentChannel']);
        $response->setData($data);
    }
}

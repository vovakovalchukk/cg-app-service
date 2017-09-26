<?php
namespace CG\Slim\Versioning\LocationEntity;

use CG\Location\Entity as LocationEntity;
use CG\Location\Type as LocationType;
use CG\Order\Service\Item\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
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

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (isset($data['type'])) {
            return;
        }
        if (isset($data['id'])) {
            $entity = $this->service->fetch($data['id']);
            $data['type'] = $entity->getType();
            $data['includeStockOnAllChannels'] = $entity->getIncludeStockOnAllChannels();
        } else {
            $data['type'] = LocationType::MERCHANT;
            $data['includeStockOnAllChannels'] = LocationEntity::DEFAULT_INCLUDE_STOCK_ON_ALL_CHANNELS;
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['type']);
        unset($data['includeStockOnAllChannels']);
        $response->setData($data);
    }
}

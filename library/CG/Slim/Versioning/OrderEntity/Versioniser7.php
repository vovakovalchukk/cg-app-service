<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser7 implements VersioniserInterface
{
    /**
     * @var Service $service
     */
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['id']) || isset($data['fulfilmentAddress1'])) {
            return;
        }

        try {
            $order = $this->service->fetch($data['id']);
            if (!$order->getFulfilmentAddress()) {
                return;
            }
            $existingData = $order->toArray();
            foreach ($existingData as $field => $value) {
                if (!$this->isFulfilmentAddressField($field)) {
                    continue;
                }
                $data[$field] = $value;
            }
            $request->setData($data);
        } catch (NotFound $e) {
            // New order so there won't be a previously set externalUsername
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        $fieldsToUnset = [];
        foreach ($data as $field => $value) {
            if (!$this->isFulfilmentAddressField($field)) {
                continue;
            }
            $fieldsToUnset[] = $field;
        }
        foreach ($fieldsToUnset as $field) {
            unset($data[$field]);
        }
        $response->setData($data);
    }

    protected function isFulfilmentAddressField($field)
    {
        return (strpos($field, 'fulfilment') === 0 && $field != 'fulfilmentChannel');
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

<?php
namespace CG\Slim\Versioning\ProductChannelDetailEntity;

use CG\Amazon\Product\ChannelDetail\External as AmazonExternalData;
use CG\Product\ChannelDetail\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser1 implements VersioniserInterface
{
    /** @var Service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['id']) || $data['channel'] != 'amazon' || isset($data['external']['fulfillmentLatency'])) {
            return;
        }

        try {
            $externalData = $this->service->fetch($data['id'])->getExternal();
            if ($externalData instanceof AmazonExternalData) {
                $data['external']['fulfillmentLatency'] = $externalData->getFulfillmentLatency();
            }
        } catch (NotFound $exception) {
            // New entity
        }

        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['external']['fulfillmentLatency']);
        return $data;
    }
}
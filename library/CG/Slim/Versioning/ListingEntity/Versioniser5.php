<?php
namespace CG\Slim\Versioning\ListingEntity;

use CG\Order\Service\Item\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser5 implements VersioniserInterface
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
        if (isset($data['id']) && !isset($data['replacedById'])) {
            try {
                $entity = $this->service->fetch($data['id']);
                $data['replacedById'] = $entity->getReplacedById();
            } catch (NotFound $exception) {
                $data['replacedById'] = null;
            }
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['replacedById']);
        $response->setData($data);
    }

    /**
     * @return self
     */
    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
}

<?php
namespace CG\Slim\Versioning\TemplateEntity;

use CG\Slim\Versioning\VersioniserInterface;
use Nocarrier\Hal;
use CG\Template\Service;

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

        unset($data['minHeight']);
        unset($data['minWidth']);
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();

        if (isset($data['paperPage'])) {
            $data['minHeight'] = $data['paperPage']['height'];
            $data['minWidth'] = $data['paperPage']['width'];
        }
        $response->setData($data);
    }
}
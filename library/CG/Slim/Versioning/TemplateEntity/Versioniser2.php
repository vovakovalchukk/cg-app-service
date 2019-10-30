<?php
namespace CG\Slim\Versioning\TemplateEntity;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;
use CG\Template\Service;

class Versioniser2 implements VersioniserInterface
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

        if (isset($data['favourite'])) {
            return;
        }
        if (!isset($data['id'])) {
            $data['favourite'] = false;
            $request->setData($data);
            return;
        }

        try {
            $template = $this->service->fetch($data['id']);
            $data['favourite'] = $template->isFavourite();
        } catch (NotFound $e) {
            $data['favourite'] = false;
        }
        $request->setData($data);
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['favourite']);
        $response->setData($data);
    }
}
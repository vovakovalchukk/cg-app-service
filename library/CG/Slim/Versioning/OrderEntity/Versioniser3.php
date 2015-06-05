<?php
namespace CG\Slim\Versioning\OrderEntity;

use CG\Order\Service\Service;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser3 implements VersioniserInterface
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

    public function upgradeRequest(array $params, Hal $request)
    {
        $entity = null;
        $data = $request->getData();
        if (!isset($data['invoiceNumber'])) {
            $data['invoiceNumber'] = $this->ensureInvoiceNumber($data);
        }
        if (!isset($data['rootOrganisationUnitId'])) {
            $data['rootOrganisationUnitId'] = $this->ensureRootOrganisationUnitId($data);
        }
        $request->setData($data);
    }

    protected function ensureInvoiceNumber(array $data)
    {
        try {
            if (!isset($data['id'])) {
                throw new NotFound();
            }
            $entity = $this->service->fetch($data['id']);
            return $entity->getInvoiceNumber();
        } catch (NotFound $e) {
            return null;
        }
    }

    protected function ensureRootOrganisationUnitId($data)
    {
        try {
            if (!isset($data['id'])) {
                throw new NotFound();
            }
            $entity = $this->service->fetch($data['id']);
            return $entity->getRootOrganisationUnitId();
        } catch (NotFound $e) {
            return null;
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['invoiceNumber']);
        unset($data['rootOrganisationUnitId']);
        $response->setData($data);
    }
}
<?php
namespace CG\Slim\Versioning\InvoiceMappingEntity;

use CG\Settings\InvoiceMapping\Service;
use CG\Slim\Versioning\VersioniserInterface;
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
        if (!isset($data['id']) || isset($data['emailSubject'], $data['emailTemplate'])) {
            return;
        }

        try {
            $invoiceMapping = $this->service->fetch($data['id']);
            $data['emailSubject'] = $data['emailSubject'] ?? $invoiceMapping->getEmailSubject();
            $data['emailTemplate'] = $data['emailTemplate'] ?? $invoiceMapping->getEmailTemplate();
            $request->setData($data);
        } catch (NotFound $exception) {
            // New mapping so there won't be a previously set email
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['emailSubject']);
        unset($data['emailTemplate']);
        $response->setData($data);
    }
}

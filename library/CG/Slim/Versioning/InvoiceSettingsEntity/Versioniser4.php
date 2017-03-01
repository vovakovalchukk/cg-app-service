<?php
namespace CG\Slim\Versioning\InvoiceSettingsEntity;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Shared\Entity as InvoiceSetting;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser4 implements VersioniserInterface
{
    /**
     * @var InvoiceSettingsService
     */
    protected $invoiceSettingsService;

    public function __construct(InvoiceSettingsService $invoiceSettingsService)
    {
        $this->setInvoiceSettingsService($invoiceSettingsService);
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['id']) || isset($data['emailTemplate'])) {
            return;
        }

        try {
            /**
             * @var InvoiceSetting $invoiceSetting
             */
            $invoiceSetting = $this->invoiceSettingsService->fetch($data['id']);
            $data['emailTemplate'] = $invoiceSetting->getEmailTemplate();
            $request->setData($data);
        } catch (NotFound $exception) {
            // New setting so there won't be a previously set email
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['emailTemplate']);
        $response->setData($data);
    }

    /**
     * @return self
     */
    protected function setInvoiceSettingsService(InvoiceSettingsService $invoiceSettingsService)
    {
        $this->invoiceSettingsService = $invoiceSettingsService;
        return $this;
    }
}

<?php
namespace CG\Slim\Versioning\InvoiceSettingsEntity;

use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Shared\Entity as InvoiceSetting;
use CG\Slim\Versioning\VersioniserInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser8 implements VersioniserInterface
{
    /**
     * @var InvoiceSettingsService
     */
    protected $invoiceSettingsService;

    public function __construct(InvoiceSettingsService $invoiceSettingsService)
    {
        $this->invoiceSettingsService = $invoiceSettingsService;
    }

    public function upgradeRequest(array $params, Hal $request)
    {
        $data = $request->getData();
        if (!isset($data['id']) || isset($data['sendToFba'])) {
            return;
        }

        try {
            /** @var InvoiceSetting $invoiceSetting */
            $invoiceSetting = $this->invoiceSettingsService->fetch($data['id']);
            $data['sendToFba'] = $invoiceSetting->getSendToFba();
            $request->setData($data);
        } catch (NotFound $exception) {
            // New setting so there won't be a previously set sendToFba setting
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['sendToFba']);
        $response->setData($data);
    }
}

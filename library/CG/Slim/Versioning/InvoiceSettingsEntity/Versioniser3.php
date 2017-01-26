<?php
namespace CG\Slim\Versioning\InvoiceSettingsEntity;

use CG\Slim\Versioning\VersioniserInterface;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Shared\Entity as InvoiceSetting;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;

class Versioniser3 implements VersioniserInterface
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
        if (!isset($data['id']) ||
            isset($data['emailSendAs'], $data['autoEmailAllowed'], $data['emailVerified'], $data['emailVerificationStatus'], $data['emailBcc'], $data['copyRequired'])
        ) {
            return;
        }

        try {
            /**
             * @var InvoiceSetting $invoiceSetting
             */
            $invoiceSetting = $this->invoiceSettingsService->fetch($data['id']);
            $data['emailSendAs'] = $invoiceSetting->getEmailSendAs();
            $data['autoEmailAllowed'] = $invoiceSetting->isAutoEmailAllowed();
            $data['emailVerified'] = $invoiceSetting->isEmailVerified();
            $data['emailVerificationStatus'] = $invoiceSetting->getEmailVerificationStatus();
            $data['emailBcc'] = $invoiceSetting->getEmailBcc();
            $data['copyRequired'] = $invoiceSetting->isCopyRequired();
            if (isset($data['tradingCompanies']) && !empty($data['tradingCompanies'])) {
                $this->upgradeTradingCompanies($data);
            }

            $request->setData($data);
        } catch (NotFound $exception) {
            // New setting so there won't be a previously set email
        }
    }

    protected function upgradeTradingCompanies(array &$data, InvoiceSetting $invoiceSetting)
    {
        $currentTradingCompanies = $invoiceSetting->getTradingCompanies();
        $newTradingCompanies = $data['tradingCompanies'];
        $data['tradingCompanies'] = [];
        foreach ($newTradingCompanies as $id => $assignedInvoice) {
            if (is_array($assignedInvoice)) {
                $data['tradingCompanies'][$id] = $assignedInvoice;

            } elseif (isset($currentTradingCompanies[$id])) {
                $data['tradingCompanies'][$id] = $currentTradingCompanies[$id];
                $data['tradingCompanies'][$id]['assignedInvoice'] = $assignedInvoice;

            } else {
                $data['tradingCompanies'][$id] = [
                    'id' => $id,
                    'assignedInvoice' => $assignedInvoice,
                    'emailSendAs' => null,
                    'emailVerified' => false,
                    'emailVerificationStatus' => null,
                ];
            }
        }
    }

    public function downgradeResponse(array $params, Hal $response, $requestedVersion)
    {
        $data = $response->getData();
        unset($data['emailSendAs'], $data['autoEmailAllowed'], $data['emailVerified'], $data['emailVerificationStatus'], $data['emailBcc'], $data['copyRequired']);
        $tradingCompanies = $data['tradingCompanies'];
        $data['tradingCompanies'] = [];
        foreach ($tradingCompanies as $id => $settings) {
            $data['tradingCompanies'][$id] = $settings['assignedInvoice'];
        }
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

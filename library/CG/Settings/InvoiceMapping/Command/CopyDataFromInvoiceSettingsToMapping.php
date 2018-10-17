<?php
namespace CG\Settings\InvoiceMapping\Command;

use CG\ETag\Exception\NotModified;
use CG\Settings\Invoice\Shared\Entity as InvoiceSettingsEntity;
use CG\Settings\Invoice\Shared\Filter as InvoiceSettingsFilter;
use CG\Settings\Invoice\Shared\Mapper as InvoiceSettingsMapper;
use CG\Settings\Invoice\Shared\Repository as InvoiceSettingsService;
use CG\Settings\InvoiceMapping\Collection as InvoiceMappingCollection;
use CG\Settings\InvoiceMapping\Entity as InvoiceMappingEntity;
use CG\Settings\InvoiceMapping\Filter as InvoiceMappingFilter;
use CG\Settings\InvoiceMapping\Service as InvoiceMappingService;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\PaginatedCollection;

class CopyDataFromInvoiceSettingsToMapping implements LoggerAwareInterface
{
    use LogTrait;

    const BATCH_SIZE = 100;

    const MAX_SAVE_RETRIES = 2;

    /** @var InvoiceSettingsService */
    protected $invoiceSettingsService;
    /** @var InvoiceMappingService */
    protected $invoiceMappingService;

    public function __construct(InvoiceSettingsService $invoiceSettingsService, InvoiceMappingService $invoiceMappingService)
    {
        $this->invoiceSettingsService = $invoiceSettingsService;
        $this->invoiceMappingService = $invoiceMappingService;
    }

    public function __invoke()
    {
        foreach ($this->fetchInvoiceSettings() as $invoiceSetting) {
            $this->updateInvoiceMappingsForSetting($invoiceSetting);
        }
    }

    /**
     * @return InvoiceSettingsEntity[]
     */
    protected function fetchInvoiceSettings(): iterable
    {
        $page = 1;
        do {
            try {
                /** @var PaginatedCollection $collection */
                $collection = $this->invoiceSettingsService->fetchCollectionByFilter(
                    (new InvoiceSettingsFilter())
                        ->setPage($page++)
                        ->setLimit(static::BATCH_SIZE)
                );
                /** @var InvoiceSettingsEntity $invoiceSettings */
                foreach ($collection as $invoiceSettings) {
                    yield $invoiceSettings;
                }
            } catch (NotFound $e) {
                break;
            }
        } while ($collection->getTotal() >= ($page - 1) * static::BATCH_SIZE);
    }

    protected function updateInvoiceMappingsForSetting(InvoiceSettingsEntity $invoiceSetting): void
    {
        $shouldUpdateMapping = false;
        if ($invoiceSetting->getEmailTemplate() != InvoiceSettingsMapper::DEFAULT_EMAIL_TEMPLATE) {
            $shouldUpdateMapping = true;
        }

        /** @var InvoiceMappingEntity $invoiceMapping */
        foreach ($this->fetchInvoiceMappingsBySetting($invoiceSetting) as $invoiceMapping) {
            if ($invoiceMapping->getSendToFba() === null || $invoiceMapping->getSendViaEmail() === null) {
                $shouldUpdateMapping = true;
            }

            if (!$shouldUpdateMapping) {
                continue;
            }

            $this->updateInvoiceMappingFromSetting($invoiceMapping, $invoiceSetting);
        }
    }

    protected function fetchInvoiceMappingsBySetting(InvoiceSettingsEntity $invoiceSetting): InvoiceMappingCollection
    {
        try {
            return $this->invoiceMappingService->fetchCollectionByFilter(
                (new InvoiceMappingFilter())
                    ->setLimit('all')
                    ->setPage(1)
                    ->setOrganisationUnitId([$invoiceSetting->getOrganisationUnitId()])
            );
        } catch (NotFound $e) {
            return new InvoiceMappingCollection(InvoiceMappingEntity::class, __FUNCTION__);
        }
    }

    protected function updateInvoiceMappingFromSetting(
        InvoiceMappingEntity $invoiceMapping,
        InvoiceSettingsEntity $invoiceSettings
    ): void {
        for ($retry = 0; $retry <= static::MAX_SAVE_RETRIES; $retry++) {
            try {
                $this->applyUpdatesToInvoiceMappingFromSetting($invoiceMapping, $invoiceSettings);
                $this->invoiceMappingService->save($invoiceMapping);
            } catch (Conflict $exception) {
                try {
                    $invoiceMapping = $this->invoiceMappingService->fetch($invoiceMapping->getId());
                } catch (NotFound $exception) {
                    $this->logWarningException($exception);
                    return;
                }
            } catch (NotFound $exception) {
                $this->logWarningException($exception);
                return;
            } catch (NotModified $exception) {
                return;
            }
        }
    }

    protected function applyUpdatesToInvoiceMappingFromSetting(
        InvoiceMappingEntity $invoiceMapping,
        InvoiceSettingsEntity $invoiceSettings
    ): void {
        if ($invoiceSettings->getEmailTemplate() != InvoiceSettingsMapper::DEFAULT_EMAIL_TEMPLATE) {
            $invoiceMapping->setEmailTemplate($invoiceSettings->getEmailTemplate());
        }

        if ($invoiceMapping->getSendToFba() === null) {
            $invoiceMapping->setSendToFba($invoiceSettings->getSendToFba());
        }

        if ($invoiceMapping->getSendViaEmail() === null) {
            $invoiceMapping->setSendViaEmail($invoiceSettings->getAutoEmail());
        }
    }
}
